{{-- Utilize the root Breeze/Jetstream authenticated application layout component --}}
<x-app-layout>
    {{-- Named slot injected into the header navigation bar of the layout --}}
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-bold text-xl text-gray-800 leading-tight" data-i18n="dashTitle">
                Polițele Mele RCA
            </h2>

            <div class="flex items-center gap-3">
                {{-- Client-side locale toggler (RO / EN) --}}
                <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200 shadow-sm text-xs font-bold">
                    <button type="button" onclick="setDashboardLanguage('ro')" id="dash-btn-ro" class="px-2.5 py-1 rounded-lg transition bg-white text-blue-600 shadow-sm">🇷🇴 RO</button>
                    <button type="button" onclick="setDashboardLanguage('en')" id="dash-btn-en" class="px-2.5 py-1 rounded-lg transition text-gray-600 hover:text-gray-900">🇬🇧 EN</button>
                </div>

                {{-- Direct call to action linking back to the RCA calculation wizard --}}
                <a href="/" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition shadow flex items-center gap-1.5" data-i18n="btnNewPolicy">
                    + Emite o poliță nouă
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-6 border border-gray-100">

                {{-- Query the authenticated user's issued policies with eager loaded offer relations sorted newest first --}}
                @php
                    $policies = Auth::user()->policies()->with('offer')->latest()->get();
                @endphp

                {{-- Display empty state guidance when no policy records are associated with the active account --}}
                @if($policies->isEmpty())
                    <div class="text-center py-12">
                        <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl font-bold shadow-sm">
                            📄
                        </div>
                        <h3 class="text-base font-bold text-gray-800" data-i18n="emptyTitle">Nu ai nicio poliță emisă</h3>
                        <p class="text-gray-500 text-xs mt-1 mb-5" data-i18n="emptySubtitle">Polițele achiziționate din calculator vor apărea automat aici.</p>
                        <a href="/" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-5 py-2.5 rounded-xl transition shadow inline-block" data-i18n="btnStartCalc">
                            Calculează și emite prima poliță &rarr;
                        </a>
                    </div>
                @else
                    {{-- Responsive Table displaying user portfolio of issued policies --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-gray-600">
                            <thead class="bg-gray-50 text-gray-700 uppercase font-black tracking-wider text-[11px] border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-4" data-i18n="thSeriesNo">Serie & Număr</th>
                                <th class="py-3 px-4" data-i18n="thInsurer">Asigurător</th>
                                <th class="py-3 px-4" data-i18n="thValidity">Valabilitate</th>
                                <th class="py-3 px-4" data-i18n="thPaid">Total Plătit</th>
                                <th class="py-3 px-4" data-i18n="thDc">Decontare Directă</th>
                                <th class="py-3 px-4 text-center" data-i18n="thActions">Acțiuni</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            {{-- Iterate over the user's active policies collection --}}
                            @foreach($policies as $p)
                                <tr class="hover:bg-gray-50/70 transition">
                                    {{-- Policy series identifier --}}
                                    <td class="py-3 px-4 font-mono font-bold text-blue-700 whitespace-nowrap">
                                        {{ $p->policy_series }} {{ $p->policy_number }}
                                    </td>
                                    {{-- Insurance carrier name --}}
                                    <td class="py-3 px-4 font-extrabold text-gray-900 uppercase">
                                        {{ $p->offer->insurer ?? 'Asigurător' }}
                                    </td>
                                    {{-- Coverage validity span --}}
                                    <td class="py-3 px-4 font-mono whitespace-nowrap">
                                        {{ $p->offer->start_date }} &mdash; {{ $p->offer->end_date }}
                                    </td>
                                    {{-- Formatted premium amount --}}
                                    <td class="py-3 px-4 font-bold text-green-700 text-sm whitespace-nowrap">
                                        {{ number_format($p->total_amount, 2) }} RON
                                    </td>
                                    {{-- Direct compensation badge indicator --}}
                                    <td class="py-3 px-4">
                                        @if($p->has_direct_compensation)
                                            <span class="inline-block bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded-full" data-i18n="tagYes">
                                                    DA
                                                </span>
                                        @else
                                            <span class="inline-block bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded-full" data-i18n="tagNo">
                                                    NU
                                                </span>
                                        @endif
                                    </td>
                                    {{-- Action link opening the generated PDF / HTML document in a new browser tab --}}
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <a href="/policy/{{ $p->id }}/view-pdf" target="_blank" class="bg-gray-900 hover:bg-black text-white text-[11px] font-bold px-3 py-1.5 rounded-lg transition shadow-sm inline-flex items-center gap-1">
                                            <span>👁️</span>
                                            <span data-i18n="btnViewDoc">Vezi Document PDF</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Script for translating RO/ENG -->
    <script>
        // Localization dictionary for dashboard headings, table headers, and badges
        const dashTranslations = {
            ro: {
                dashTitle: "Polițele Mele RCA",
                btnNewPolicy: "+ Emite o poliță nouă",
                emptyTitle: "Nu ai nicio poliță emisă",
                emptySubtitle: "Polițele achiziționate din calculator vor apărea automat aici.",
                btnStartCalc: "Calculează și emite prima poliță →",
                thSeriesNo: "Serie & Număr",
                thInsurer: "Asigurător",
                thValidity: "Valabilitate",
                thPaid: "Total Plătit",
                thDc: "Decontare Directă",
                thActions: "Acțiuni",
                tagYes: "DA",
                tagNo: "NU",
                btnViewDoc: "Vezi Document PDF"
            },
            en: {
                dashTitle: "My RCA Policies",
                btnNewPolicy: "+ Issue New Policy",
                emptyTitle: "No policies issued yet",
                emptySubtitle: "Policies purchased via the calculator will appear here automatically.",
                btnStartCalc: "Calculate and issue your first policy →",
                thSeriesNo: "Series & Number",
                thInsurer: "Insurer",
                thValidity: "Validity",
                thPaid: "Total Paid",
                thDc: "Direct Compensation",
                thActions: "Actions",
                tagYes: "YES",
                tagNo: "NO",
                btnViewDoc: "View PDF Document"
            }
        };

        let currentDashLang = localStorage.getItem('app_lang') || 'ro';

        /**
         * Switches active dashboard language, updates button styles, and translates elements
         */
        function setDashboardLanguage(lang) {
            currentDashLang = lang;
            localStorage.setItem('app_lang', lang);

            const btnRo = document.getElementById('dash-btn-ro');
            const btnEn = document.getElementById('dash-btn-en');

            if (btnRo && btnEn) {
                btnRo.className = lang === 'ro'
                    ? "px-2.5 py-1 rounded-lg transition bg-white text-blue-600 shadow-sm"
                    : "px-2.5 py-1 rounded-lg transition text-gray-600 hover:text-gray-900";
                btnEn.className = lang === 'en'
                    ? "px-2.5 py-1 rounded-lg transition bg-white text-blue-600 shadow-sm"
                    : "px-2.5 py-1 rounded-lg transition text-gray-600 hover:text-gray-900";
            }

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (dashTranslations[lang][key]) {
                    el.innerText = dashTranslations[lang][key];
                }
            });
        }

        // Apply preserved language choice on DOM load
        document.addEventListener('DOMContentLoaded', () => {
            setDashboardLanguage(currentDashLang);
        });
    </script>
</x-app-layout>
