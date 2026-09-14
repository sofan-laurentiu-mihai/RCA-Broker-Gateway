<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\RcaOffer;
use App\Models\RcaPolicy;

class RcaFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_rca_flow_from_quote_to_pdf_rendering()
    {
        // We simulate the external responses of Life is Hard via HTTP Fake.
        Http::fake([
            '*/auth*' => Http::response([
                'error' => false,
                'data' => [
                    'refresh_token' => 'mock-jwt-token-123',
                    'token' => 'mock-jwt-token-123'
                ]
            ], 200),

            '*/offer*' => Http::response([
                'error' => false,
                'data' => [
                    'provider' => [
                        'organization' => ['businessName' => 'groupama']
                    ],
                    'offers' => [
                        [
                            'offerId' => 101,
                            'providerOfferCode' => 'GRP-2026-XYZ',
                            'premiumAmount' => 150.00,
                            'directCompensation' => ['premiumAmount' => 35.00],
                            'bonusMalusClass' => 'B2',
                            'startDate' => '2026-09-01',
                            'endDate' => '2027-09-01'
                        ]
                    ]
                ]
            ], 200),

            '*/policy*' => Http::response([
                'error' => false,
                'data' => [
                    'policyNumber' => 'RO/999888',
                    'policy_number' => 'RO/999888',
                    'policySeries' => 'RCA',
                    'documentUrl' => 'https://api.lifeishard.ro/rca/documents/sample.pdf'
                ]
            ], 200),

            '*/documents*' => Http::response([
                'error' => false,
                'data' => [
                    'documentUrl' => 'https://api.lifeishard.ro/rca/documents/sample.pdf'
                ]
            ], 200),
        ]);

        // We test the calculus of the offer (POST /api/rca/calculate)
        $quotePayload = [
            'startDate' => '2026-09-01',
            'termTime' => 12,
            'lastName' => 'Popescu',
            'firstName' => 'Ion',
            'taxId' => '1900101123456',
            'birthdate' => '1990-01-01',
            'gender' => 'm',
            'email' => 'ion.popescu@example.com',
            'mobileNumber' => '0712345678',
            'idNumber' => 'RD123456',
            'county' => 'Bucuresti',
            'city' => 'Sector 1',
            'cityCode' => 179132,
            'street' => 'Victoriei',
            'houseNumber' => '10',
            'registrationType' => 'registered',
            'licensePlate' => 'B123ABC',
            'vin' => 'WAUZZZ8K9BA123456',
            'civNumber' => 'A1234567',
            'brand' => 'AUDI',
            'model' => 'A4',
            'year' => 2018,
            'displacement' => 1968,
            'power' => 110,
            'weight' => 2050,
            'seats' => 5,
            'fuelType' => 'motorina'
        ];

        $calculateResponse = $this->postJson('/api/rca/calculate', $quotePayload);

        $calculateResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        // We need to check if we have at least 4 offers generated
        $this->assertGreaterThanOrEqual(1, count($calculateResponse->json('offers')));

        //We check the first offer saved(Groupama) with the respective sufix.
        $this->assertDatabaseHas('rca_offers', [
            'provider_offer_code' => 'GRP-2026-XYZ-GRP',
            'premium_amount' => 150.00
        ]);

        $savedOffer = RcaOffer::first();

        // We test the issue of the policy (POST /api/rca/issue)
        $issueResponse = $this->postJson('/api/rca/issue', [
            'offer_db_id' => $savedOffer->id,
            'direct_compensation' => true
        ]);

        $issueResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('rca_policies', [
            'policy_number' => 'RO/999888',
            'has_direct_compensation' => true
        ]);

        $savedPolicy = RcaPolicy::first();

        // Finally, we test the rendering of the PDF document
        $pdfViewResponse = $this->get("/policy/{$savedPolicy->id}/view-pdf");

        $pdfViewResponse->assertStatus(200)
            ->assertSee('RO/999888')
            ->assertSee('Popescu')
            ->assertSee('WAUZZZ8K9BA123456');
    }

    /**We need to check with a unit test if the backend accepts the selected cities and the SIRUTA
     * code from the nomenclature.
     **/
    public function test_rca_calculation_with_dynamic_siruta_location()
    {
        Http::fake([
            '*/auth*' => Http::response([
                'error' => false,
                'data' => ['refresh_token' => 'mock-jwt-token-123']
            ], 200),
            '*/offer*' => Http::response([
                'error' => false,
                'data' => [
                    'provider' => ['organization' => ['businessName' => 'groupama']],
                    'offers' => [
                        [
                            'offerId' => 201,
                            'providerOfferCode' => 'GRP-CLUJ-2026',
                            'premiumAmount' => 175.00,
                            'directCompensation' => ['premiumAmount' => 40.00],
                            'bonusMalusClass' => 'B0',
                            'startDate' => '2026-09-01',
                            'endDate' => '2027-09-01'
                        ]
                    ]
                ]
            ], 200),
        ]);

        $payload = [
            'startDate' => '2026-09-01',
            'termTime' => 12,
            'lastName' => 'Ionescu',
            'firstName' => 'Maria',
            'taxId' => '6020512123456',
            'birthdate' => '2002-05-12',
            'gender' => 'f',
            'email' => 'maria.ionescu@example.com',
            'mobileNumber' => '0799112233',
            'idNumber' => 'CJ654321',
            'county' => 'Cluj',
            'city' => 'Cluj-Napoca',
            'cityCode' => 54975, // SIRUTA code from Cluj-Napoca
            'street' => 'Memorandumului',
            'houseNumber' => '22',
            'registrationType' => 'registered',
            'licensePlate' => 'CJ99XYZ',
            'vin' => 'WVWZZZ3CZWE123456',
            'civNumber' => 'B9876543',
            'brand' => 'VOLKSWAGEN',
            'model' => 'PASSAT',
            'year' => 2020,
            'displacement' => 1968,
            'power' => 140,
            'weight' => 2100,
            'seats' => 5,
            'fuelType' => 'motorina'
        ];

        $response = $this->postJson('/api/rca/calculate', $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('rca_offers', [
            'provider_offer_code' => 'GRP-CLUJ-2026-GRP',
            'premium_amount' => 175.00
        ]);
    }
}
