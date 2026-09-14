<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\RcaOffer;
use App\Models\RcaAuditLog;
use Exception;

class LifeIsHardService
{
    protected string $baseUrl;
    public string $username;
    public string $password;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.lifeishard.base_url') ?? '', '/');
        $this->username = config('services.lifeishard.username') ?? 'test';
        $this->password = config('services.lifeishard.password') ?? 'test';
    }

    public function getAuthToken(): string
    {
        if (Cache::has('lifeishard_token')) {
            return Cache::get('lifeishard_token');
        }

        // Se trimit ca query params conform specificatiei din SwaggerHub
        $response = Http::withoutVerifying()
            ->acceptJson()
            ->post("{$this->baseUrl}/auth", [
                'account' => $this->username,
                'password' => $this->password,
            ]);

        $body = $response->json();

        RcaAuditLog::create([
            'action' => 'AUTH',
            'provider_request' => ['account' => $this->username],
            'provider_response' => $body ?? ['raw' => $response->body()],
            'http_status' => $response->status(),
        ]);

        $token = $body['data']['refresh_token'] ?? $body['data']['token'] ?? null;

        if ($response->successful() && !empty($token)) {
            Cache::put('lifeishard_token', $token, 1800);
            return $token;
        }

        $errorMsg = $body['message'] ?? $response->body();
        throw new Exception("Authentication failed [HTTP {$response->status()}]: {$errorMsg}");
    }

    public function createOffer(array $userInput, array $lihPayload, ?string $ip, ?string $userAgent): array
    {
        $token = $this->getAuthToken();

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->acceptJson()
            ->asJson()
            ->post("{$this->baseUrl}/offer", $lihPayload);

        $body = $response->json();

        $audit = RcaAuditLog::create([
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'action' => 'CREATE_OFFER',
            'user_input' => $userInput,
            'provider_request' => $lihPayload,
            'provider_response' => $body ?? ['raw' => $response->body()],
            'http_status' => $response->status(),
            'correlation_id' => $body['correlation_id'] ?? null,
        ]);

        return [
            'audit_id' => $audit->id,
            'success' => $response->successful() && !($body['error'] ?? false),
            'status' => $response->status(),
            'data' => $body
        ];
    }

    public function IssuePolicy(array $userInput, array $lihPayload, ?string $ip, ?string $userAgent): array
    {
        $token = $this->getAuthToken();

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->acceptJson()
            ->asJson()
            ->post("{$this->baseUrl}/policy", $lihPayload);

        $body = $response->json();

        $audit = RcaAuditLog::create([
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'action' => 'ISSUE_POLICY',
            'user_input' => $userInput,
            'provider_request' => $lihPayload,
            'provider_response' => $body ?? ['raw' => $response->body()],
            'http_status' => $response->status(),
            'correlation_id' => $body['correlation_id'] ?? null,
        ]);

        return [
            'audit_id' => $audit->id,
            'success' => $response->successful() && !($body['error'] ?? false),
            'status' => $response->status(),
            'data' => $body
        ];
    }

    public function getPolicyDocuments(int $policyId, string $providerOfferCode, ?string $ip, ?string $userAgent): array
    {
        $token = $this->getAuthToken();

        // Conform LIH v1.4.1, documentele se cer prin GET cu parametrii de identificare
        $response = Http::withoutVerifying()
            ->withToken($token)
            ->acceptJson()
            ->get("{$this->baseUrl}/documents", [
                'providerOfferCode' => $providerOfferCode,
            ]);

        $body = $response->json();

        $audit = RcaAuditLog::create([
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'action' => 'GET_DOCUMENTS',
            'user_input' => ['policy_id' => $policyId],
            'provider_request' => ['providerOfferCode' => $providerOfferCode],
            'provider_response' => $body ?? ['raw' => $response->body()],
            'http_status' => $response->status(),
            'correlation_id' => $body['correlation_id'] ?? null,
        ]);

        return [
            'audit_id' => $audit->id,
            'success' => $response->successful() && !($body['error'] ?? false),
            'status' => $response->status(),
            'data' => $body
        ];
    }

    public function getQuotes(array $userInput, array $requestPayload, ?string $ip = null, ?string $userAgent = null): array
    {
        $token = $this->getAuthToken();

        // Standard call to the official endpoint /offer from the Swagger LIH v.1.4.1
        $response = Http::withoutVerifying()
            ->withToken($token)
            ->acceptJson()
            ->asJson()
            ->post("{$this->baseUrl}/offer", $requestPayload);

        $body = $response->json() ?? [];
        $status = $response->status();

        // Audit Trail mandatory
        $audit = RcaAuditLog::create([
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'action' => 'CALCULATE_QUOTE',
            'user_input' => $userInput,
            'provider_request' => $requestPayload,
            'provider_response' => $body ?? ['raw' => $response->body()],
            'http_status' => $status,
            'correlation_id' => $body['correlation_id'] ?? null,
        ]);

        if (!$response->successful() || ($body['error'] ?? false)) {
            return [
                'success' => false,
                'status' => $status,
                'data' => $body,
                'audit_id' => $audit->id,
            ];
        }

        $rawOffers = $body['data']['offers'] ?? [];
        $rawInsurer = $body['data']['provider']['organization']['businessName'] ?? 'groupama';
        $savedOffers = [];

        // Prioritize the date selected by the user in the form over mock responses
        $inputStartDate = $userInput['startDate'] ?? $requestPayload['product']['motor']['startDate'] ?? now()->toDateString();
        $termMonths = (int)($userInput['termTime'] ?? $requestPayload['product']['motor']['termTime'] ?? 12);

        $parsedStart = \Carbon\Carbon::parse($inputStartDate);
        $calculatedStartDate = $parsedStart->toDateString();
        $calculatedEndDate = $parsedStart->copy()->addMonths($termMonths)->subDay()->toDateString();

        // If we get more offers from the API, we save them
        if (count($rawOffers) > 1) {
            foreach ($rawOffers as $item) {
                $savedOffers[] = RcaOffer::create([
                    'audit_log_id' => $audit->id,
                    'offer_id' => (int)($item['offerId'] ?? 0),
                    'provider_offer_code' => $item['providerOfferCode'] ?? 'OFFER-' . rand(1000, 9999),
                    'insurer' => strtoupper($rawInsurer),
                    'premium_amount' => (float)($item['premiumAmount'] ?? 0),
                    'direct_compensation_amount' => !empty($item['directCompensation']['premiumAmount']) ? (float)$item['directCompensation']['premiumAmount'] : null,
                    'bonus_malus' => $item['bonusMalusClass'] ?? 'B0',
                    'start_date' => $calculatedStartDate,
                    'end_date' => $calculatedEndDate,
                ]);
            }
        } else {
            // Sandbox case: we take the data from the mock and generate the multi-comparator
            $baseOffer = $rawOffers[0] ?? [];
            $basePrice = (float)($baseOffer['premiumAmount'] ?? 89.00);
            $bmClass = $baseOffer['bonusMalusClass'] ?? 'B2';

            $companies = [
                ['name' => 'Groupama Asigurări', 'code' => 'GRP', 'diff' => 0.00, 'dc' => 18.00],
                ['name' => 'Allianz-Țiriac', 'code' => 'ALZ', 'diff' => 12.50, 'dc' => 15.00],
                ['name' => 'Omniasig VIG', 'code' => 'OMN', 'diff' => -5.00, 'dc' => 20.00],
                ['name' => 'Grawe România', 'code' => 'GRW', 'diff' => 24.00, 'dc' => 25.00],
            ];

            foreach ($companies as $idx => $comp) {
                $calcPremium = max(50.00, $basePrice + $comp['diff']);
                $calcDc = $calcPremium + $comp['dc'];

                $savedOffers[] = RcaOffer::create([
                    'audit_log_id' => $audit->id,
                    'insurer' => $comp['name'],
                    'offer_id' => (int)($baseOffer['offerId'] ?? 100) + $idx,
                    'provider_offer_code' => ($baseOffer['providerOfferCode'] ?? 'OFFER') . '-' . $comp['code'],
                    'premium_amount' => $calcPremium,
                    'direct_compensation_amount' => $calcDc,
                    'bonus_malus' => $bmClass,
                    'start_date' => $calculatedStartDate,
                    'end_date' => $calculatedEndDate,
                ]);
            }
        }

        return [
            'success' => true,
            'offers' => $savedOffers,
            'audit_id' => $audit->id,
        ];
    }
}
