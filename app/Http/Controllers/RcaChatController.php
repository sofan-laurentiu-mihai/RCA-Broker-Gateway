<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RcaChatController extends Controller
{

    /**
     * This handles incoming chatbot inquiries and grabs AI-generated answers
     * or fallback mocks
     */
    public function ask(Request $request)
    {
        /** 1. We validate incoming chat payload to prevent payload abuse or the
         * excess of long prompts
         */
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array'
        ]);

        $userMessage = $request->input('message');
        // With this, we retrieve the Gemini API key from environment variables or application config.
        $geminiKey = env('GEMINI_API_KEY') ?: config('services.gemini.key');

        /** If no API key is returned, we route it to the internal offline basic
         * answers to basic questions
         */
        if (empty($geminiKey)) {
            return response()->json([
                'success' => true,
                'reply' => $this->getMockAnswer($userMessage)
            ]);
        }

        /** 3. With this, we give to the agent a prompt to instruct it of what he needs to do,
         * and, in this case, with the romanian RCA regulations
         */
        try {
            $prompt = "Ești un asistent AI expert în legislația și piața polițelor RCA din România (norme ASF și BAAR). "
                . "Răspunde clar, prietenos și concis în maxim 2 paragrafe la următoarea întrebare:\n\n"
                . $userMessage;

            // 4. We send the request via Laravel HTTP client to the Gemini Generative Language API
            $response = Http::withoutVerifying()
                ->timeout(20)
                ->acceptJson()
                ->asJson()
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key={$geminiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

            // 5. Then, we dissect the AI response and to filter out any internal reasoning tokens if present
            if ($response->successful()) {
                $data = $response->json();
                $parts = $data['candidates'][0]['content']['parts'] ?? [];

                // It prioritizes natural language output by ignoring internal thought blocks
                foreach ($parts as $part) {
                    if (!empty($part['text']) && empty($part['thought'])) {
                        return response()->json([
                            'success' => true,
                            'reply' => trim($part['text'])
                        ]);
                    }
                }

                // Fallback to the first available text block if structure differs
                if (!empty($parts[0]['text'])) {
                    return response()->json([
                        'success' => true,
                        'reply' => trim($parts[0]['text'])
                    ]);
                }
            }

            // Return mock response if the API responds with a non-200 status code
            return response()->json([
                'success' => true,
                'reply' => $this->getMockAnswer($userMessage)
            ]);

        } catch (\Throwable $e) {
            // Guarantee high availability with a return mock if the external
            // call throws an exception
            return response()->json([
                'success' => true,
                'reply' => $this->getMockAnswer($userMessage)
            ]);
        }
    }

    /**
     * We constructed a rule-based fallback system that provides deterministic answers
     * for common RCA questions
     */
    private function getMockAnswer(string $msg): string
    {
        // Normalize search string to lowercase for case-insensitive matching
        $m = mb_strtolower($msg);

        // For direct compensation inquiries
        if (str_contains($m, 'decontare') || str_contains($m, 'directa')) {
            return "Decontarea directă este o clauză opțională care îți permite ca, în caz de accident în care ești nevinovat, să îți repari mașina direct pe propriul tău RCA, fără a mai merge la asigurătorul șoferului vinovat.";
        }

        // For Bonus-Malus rating inquiries
        if (str_contains($m, 'bonus') || str_contains($m, 'malus') || str_contains($m, 'clasa')) {
            return "Dacă ai fost vinovat într-un accident, sistemul Bonus-Malus te penalizează cu fix 2 clase de malus (de exemplu de la B2 la B0, sau de la B0 la M2) la următoarea reînnoire a poliței RCA.";
        }

        // For vehicle repair and claim resolution questions
        if (str_contains($m, 'refac') || str_contains($m, 'repar') || str_contains($m, 'service')) {
            return "Dacă ești nevinovat, repari mașina prin asigurarea RCA a vinovatului (sau pe RCA-ul tău dacă ai Decontare Directă). Dacă ai fost vinovat, reparația mașinii proprii se face doar prin polița ta CASCO sau din fonduri proprii.";
        }

        // Default generic message when keywords to not match
        return "Sunt asistentul tău virtual pentru polițe RCA. Te pot ajuta cu informații despre sistemul Bonus-Malus, Decontarea Directă, valabilitatea poliței sau pașii de urmat în caz de accident!";
    }
}
