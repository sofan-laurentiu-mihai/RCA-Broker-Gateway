<?php

namespace App\Http\Controllers;

use App\Models\RcaPolicy;
use Illuminate\Http\Request;
use App\Services\LifeIsHardService;
use App\Models\RcaOffer;
use App\Mail\PolicyIssuedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RcaCalculatorController extends Controller
{
    // A dependency which handles API calls to the Life is Hard platform
    protected LifeIsHardService $lihService;

    public function __construct(LifeIsHardService $lihService)
    {
        $this->lihService = $lihService;
    }

    public function getQuotes(Request $request)
    {
        /** 1. We will validate all the field in conformity to the Romanian
         insurance standards **/

        $validated = $request->validate([
            'startDate' => 'required|date',
            'termTime' => 'required|integer',
            'lastName' => 'required|string',
            'firstName' => 'required|string',
            'taxId' => 'required|string|size:13',
            'birthdate' => 'required|date',
            'gender' => 'required|in:m,f',
            'email' => 'required|email',
            'mobileNumber' => 'required|string',
            'idNumber' => 'required|string',
            'county' => 'required|string',
            'city' => 'required|string',
            'cityCode' => 'required|integer',
            'street' => 'required|string',
            'houseNumber' => 'required|string',
            'registrationType' => 'required|in:registered,temporaryRegistered',
            'licensePlate' => 'nullable|string',
            'vin' => 'required|string|min:10',
            'civNumber' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer',
            'displacement' => 'required|integer',
            'power' => 'required|integer',
            'weight' => 'required|integer',
            'seats' => 'required|integer',
            'fuelType' => 'required|string',
            'insurer' => 'nullable|string',
        ]);

        // 2. We will map the form data to the nested JSON required by the Life is Hard API
        $lihPayload = [
            'provider' => [
                'organization' => [
                    'businessName' => $validated['insurer'] ?? 'groupama',
                ]
            ],
            'product' => [
                'motor' => [
                    'startDate' => $validated['startDate'],
                    'termTime' => (int)$validated['termTime'],
                ],
                'policyholder' => [
                    'lastName' => $validated['lastName'],
                    'firstName' => $validated['firstName'],
                    'taxId' => $validated['taxId'],
                    'birthdate' => $validated['birthdate'],
                    'gender' => $validated['gender'],
                    'email' => $validated['email'],
                    'mobileNumber' => $validated['mobileNumber'],
                    'identification' => [
                        'idType' => 'CI',
                        'idNumber' => $validated['idNumber'],
                    ],
                    'address' => [
                        'country' => 'RO',
                        'county' => $validated['county'],
                        'city' => $validated['city'],
                        'cityCode' => (int)$validated['cityCode'],
                        'street' => $validated['street'],
                        'houseNumber' => $validated['houseNumber'],
                    ]
                ],
                'vehicle' => [
                    'registrationType' => $validated['registrationType'],
                    'licensePlate' => $validated['licensePlate'] ?? null,
                    'vin' => $validated['vin'],
                    'identification' => [
                        'idType' => 'CIV',
                        'idNumber' => $validated['civNumber'],
                    ],
                    'vehicleType' => 'M1',
                    'brand' => strtoupper($validated['brand']),
                    'model' => strtoupper($validated['model']),
                    'yearOfConstruction' => (int)$validated['year'],
                    'engineDisplacement' => (int)$validated['displacement'],
                    'enginePower' => (int)$validated['power'],
                    'totalWeight' => (int)$validated['weight'],
                    'seats' => (int)$validated['seats'],
                    'fuelType' => $validated['fuelType'],
                    'usageType' => 'personal',
                ]
            ]
        ];

        // 3. We deelegate the quote request and audit log to the Life is Hard service
        $result = $this->lihService->getQuotes(
            $validated,
            $lihPayload,
            $request->ip(),
            $request->userAgent()
        );

        // It will return an error response if the comms. with the provider fails
        if (empty($result['success'])) {
            return response()->json([
                'success' => false,
                'message' => $result['data']['message'] ?? 'Offer error.',
                'errors' => $result['data'] ?? null
            ], $result['status'] ?? 500);
        }

        // We return the offers directly and structured in the service
        return response()->json([
            'success' => true,
            'offers' => $result['offers'],
        ]);
    }

    /**
     * It will issue an active policy, it will prevent duplicated VIN policies per user,
     * and we will send an email confirmation
     */
    public function issuePolicy(Request $request)
    {
        $validated = $request->validate([
            'offer_db_id' => 'required|exists:rca_offers,id',
            'direct_compensation' => 'required|boolean',
        ]);

        $offer = RcaOffer::with('auditLog')->findOrFail($validated['offer_db_id']);

        /**
         * We reconstruct the details of the vehicle and owner from the audit log
         */
        $auditLog = $offer->auditLog;
        $userInput = $auditLog ? ($auditLog->user_input ?? []) : [];
        if (is_string($userInput)) {
            $userInput = json_decode($userInput, true) ?? [];
        }

        $providerReq = $auditLog ? ($auditLog->provider_request ?? []) : [];
        if (is_string($providerReq)) {
            $providerReq = json_decode($providerReq, true) ?? [];
        }

        /**
         * We verify if the user already purchased an active policy for this VIN
         * Once a policy is issued for a VIN, the user must change at least one character to issue another
         */
        $currentVin = strtoupper(trim($userInput['vin'] ?? ''));
        if (Auth::check() && !empty($currentVin)) {
            // We look through user policies and check the VIN inside the offer audit log
            $alreadyPurchased = RcaPolicy::where('user_id', Auth::id())
                ->whereHas('offer.auditLog', function ($query) use ($currentVin) {
                    $query->where('user_input', 'like', '%' . $currentVin . '%');
                })
                ->exists();

            if ($alreadyPurchased) {
                return response()->json([
                    'success' => false,
                    'message' => "You already have a active policy issued for this VIN {$currentVin}. Set another VIN for the checking in the form!",
                ], 422);
            }
        }

        // We need to prepare the provider issuing params.
        $lihPayload = [
            'offerId' => (int)$offer->offer_id,
            'providerOfferCode' => (string)$offer->provider_offer_code,
            'directCompensation' => (bool)$validated['direct_compensation'],
        ];

        // It will execute policy issue request on the external API
        $result = $this->lihService->issuePolicy(
            $validated,
            $lihPayload,
            $request->ip(),
            $request->userAgent()
        );

        if (empty($result['success'])) {
            return response()->json([
                'success' => false,
                'message' => $result['data']['message'] ?? 'Policy emitting error.',
                'errors' => $result['data'] ?? null
            ], $result['status'] ?? 500);
        }

        $policyData = $result['data']['data'] ?? [];

        // Calculates total amount based on direct compensation
        $totalAmount = $validated['direct_compensation']
            ? ($offer->direct_compensation_amount ?? $offer->premium_amount)
            : $offer->premium_amount;

        $policyNumber = $policyData['policyNumber'] ?? $policyData['policy_number'] ?? 'RCA';
        $policySeries = $policyData['policySeries'] ?? $policyData['policy_series'] ?? 'RCA';

        // We create the policy and do the direct association with user id
        $policy = RcaPolicy::create([
            'offer_id' => $offer->id,
            'audit_log_id' => $result['audit_id'],
            'user_id' => Auth::id(),
            'policy_number' => $policyData['policy_number'] ?? 'RO/' . rand(100000, 999999),
            'policy_series' => $policyData['policySeries'] ?? 'RCA',
            'status' => 'issued',
            'document_url' => $policyData['documentUrl'] ?? null,
            'total_amount' => $totalAmount,
            'has_direct_compensation' => (bool)$validated['direct_compensation'],
        ]);

        /**
         * We determine the e-mail address (the priority is connected account -> e-mail from the form)
         */
        $recipientEmail = Auth::check()
            ? Auth::user()->email
            : ($userInput['email'] ?? null);

        if (!empty($recipientEmail)) {
            // We structure the details for the blade template of the Document
            $policyholder = $providerReq['product']['policyholder'] ?? [
                'lastName' => $userInput['lastName'] ?? '-',
                'firstName' => $userInput['firstName'] ?? '-',
                'taxId' => $userInput['taxId'] ?? '-',
                'email' => $userInput['email'] ?? '-',
                'identification' => [
                    'idType' => 'CI',
                    'idNumber' => $userInput['idNumber'] ?? '-'
                ],
                'address' => [
                    'street' => $userInput['street'] ?? '-',
                    'houseNumber' => $userInput['houseNumber'] ?? '',
                    'city' => $userInput['city'] ?? '-',
                    'county' => $userInput['county'] ?? '-'
                ]
            ];

            $vehicle = $providerReq['product']['vehicle'] ?? [
                'brand' => $userInput['brand'] ?? '-',
                'model' => $userInput['model'] ?? '-',
                'licensePlate' => $userInput['licensePlate'] ?? '-',
                'vin' => $userInput['vin'] ?? '-',
                'identification' => [
                    'idNumber' => $userInput['civNumber'] ?? '-'
                ],
                'engineDisplacement' => $userInput['displacement'] ?? '-',
                'enginePower' => $userInput['power'] ?? '-'
            ];

            // We render the policy document using the same view as for the viewing of the PDF
            $documentHtml = view('policy_document', [
                'policy' => $policy,
                'policyholder' => $policyholder,
                'vehicle' => $vehicle,
            ])->render();

            // Protected expedition in try-catch to not cancel the answer if the SMTP server fails
            try {
                Mail::to($recipientEmail)->send(new PolicyIssuedMail($policy, $documentHtml));
            } catch (\Throwable $mailEx) {
                Log::error("Error to transmit e-mail for policy #{$policy->id}: " . $mailEx->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Policy emitting success.",
            'policy' => $policy,
        ]);
    }

    /**
     * It retrieves document links/binaries from Life is Hard service and
     * updates local policy record
     */
    public function getPolicyDocument(int $id, Request $request)
    {
        $policy = RcaPolicy::with('offer')->findOrFail($id);

        $result = $this->lihService->getPolicyDocuments(
            $policy->id,
            (string)$policy->offer->provider_offer_code,
            $request->ip(),
            $request->userAgent()
        );

        $docs = $result['data']['data'] ?? [];

        // We have a fallback URL doc. simulation for mock or testing environments
        $documentUrl = $docs['documentUrl']
            ?? $docs['policyUrl']
            ?? 'https://api.lifeishard.ro/rca/documents/sample-policy-' . $policy->policy_number . '.pdf';

        $policy->update(['document_url' => $documentUrl]);

        return response()->json([
            'success' => true,
            'policy_id' => $policy->id,
            'policy_number' => $policy->policy_number,
            'document_url' => $documentUrl,
            'raw_documents' => $docs
        ]);
    }

    /**
     * It lists a paginated history of all API audit logs for administration
     */
    public function listAuditLogs()
    {
        $logs = \App\Models\RcaAuditLog::latest()->paginate(20);

        return view('audit_logs', compact('logs'));
    }

    /**
     * It displays complete details and JSON payloads for a specific audit log record.
     */
    public function viewAuditLog($id)
    {
        $log = \App\Models\RcaAuditLog::findOrFail($id);

        return view('audit_log_view', compact('log'));
    }

    /**
     * We will reconstruct the policy data and will render the PDF view
     */
    public function viewPdfDocument(int $id)
    {
        $policy = RcaPolicy::with(['offer.auditLog', 'auditLog'])->findOrFail($id);

        // We take the log of the offer with owner and vehicle data.
        $auditLog = $policy->offer->auditLog ?? $policy->auditLog;

        $providerReq = $auditLog->provider_request ?? [];
        if (is_string($providerReq)) {
            $providerReq = json_decode($providerReq, true) ?? [];
        }

        $userInput = $auditLog->user_input ?? [];
        if (is_string($userInput)) {
            $userInput = json_decode($userInput, true) ?? [];
        }

        // We extract the information of the owner with respect to both Life is Hard format and simple form format.
        $policyholder = $providerReq['product']['policyholder'] ?? [
            'lastName' => $userInput['lastName'] ?? '-',
            'firstName' => $userInput['firstName'] ?? '-',
            'taxId' => $userInput['taxId'] ?? '-',
            'email' => $userInput['email'] ?? '-',
            'identification' => [
                'idType' => 'CI',
                'idNumber' => $userInput['idNumber'] ?? '-'
            ],
            'address' => [
                'street' => $userInput['street'] ?? '-',
                'houseNumber' => $userInput['houseNumber'] ?? '',
                'city' => $userInput['city'] ?? '-',
                'county' => $userInput['county'] ?? '-'
            ]
        ];

        // We extract the information of the vehicle with respect to both Life is Hard format or simple form format.
        $vehicle = $providerReq['product']['vehicle'] ?? [
            'brand' => $userInput['brand'] ?? '-',
            'model' => $userInput['model'] ?? '-',
            'licensePlate' => $userInput['licensePlate'] ?? '-',
            'vin' => $userInput['vin'] ?? '-',
            'identification' => [
                'idNumber' => $userInput['civNumber'] ?? '-'
            ],
            'engineDisplacement' => $userInput['displacement'] ?? '-',
            'enginePower' => $userInput['power'] ?? '-'
        ];


        return view('policy_document', [
            'policy' => $policy,
            'policyholder' => $policyholder,
            'vehicle' => $vehicle,
        ]);
    }


}
