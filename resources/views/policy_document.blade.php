<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {{-- Dynamic title utilizing policy series and number attributes --}}
    <title data-i18n="docTitle">RCA Insurance Policy - {{ $policy->policy_series }} {{ $policy->policy_number }}</title>
    {{-- Load Tailwind CSS via CDN for rapid responsive layout prototyping --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Print media styles ensuring a clean page printout without UI controls */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: white; }
            .policy-container { box-shadow: none !important; border: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-100 p-4 sm:p-10 font-sans text-gray-800">

<div class="max-w-4xl mx-auto bg-white p-6 sm:p-12 rounded-2xl shadow-xl border border-gray-200 policy-container relative">

    {{-- Action Bar: hidden during browser printing --}}
    <div class="no-print flex flex-wrap justify-between items-center gap-4 mb-8 pb-4 border-b border-gray-200">
        <span class="text-sm font-bold text-gray-500" data-i18n="officialDoc">Official Online Issued Document</span>

        <div class="flex items-center gap-3">
            {{-- Client-side locale toggler (RO / EN) --}}
            <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200 shadow-sm text-xs font-bold">
                <button type="button" onclick="setLanguage('ro')" id="btn-ro" class="px-2.5 py-1 rounded-lg transition text-gray-600 hover:text-gray-900">🇷🇴 RO</button>
                <button type="button" onclick="setLanguage('en')" id="btn-en" class="px-2.5 py-1 rounded-lg transition bg-white text-blue-600 shadow-sm">🇬🇧 EN</button>
            </div>

            {{-- Native window print trigger --}}
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-xl text-xs sm:text-sm transition shadow flex items-center gap-1.5 active:scale-95">
                <span data-i18n="btnPrint">🖨️ Print / Save as PDF</span>
            </button>
        </div>
    </div>

    {{-- Document Header: Title, CEDAM mention, and Policy Identification --}}
    <div class="flex justify-between items-start border-b-2 border-gray-900 pb-6 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900" data-i18n="heading">RCA MOTOR THIRD PARTY LIABILITY POLICY</h1>
            <p class="text-xs text-gray-500 uppercase tracking-widest mt-1" data-i18n="subheading">Registered in the CEDAM National Database</p>
        </div>
        <div class="text-right">
            <span class="text-xs text-gray-400 font-bold block uppercase" data-i18n="lblSeriesNo">Series & Number</span>
            <span class="text-xl sm:text-2xl font-mono font-black text-blue-700">{{ $policy->policy_series }} {{ $policy->policy_number }}</span>
        </div>
    </div>

    {{-- Insurer & Validity Overview --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-xl mb-6 border border-gray-200 text-sm">
        <div>
            <span class="text-xs text-gray-400 font-bold uppercase block" data-i18n="lblInsurer">Insurer</span>
            <span class="font-extrabold text-gray-900 text-base uppercase">{{ $policy->offer->insurer }}</span>
        </div>
        <div>
            <span class="text-xs text-gray-400 font-bold uppercase block" data-i18n="lblStartDate">Start Date</span>
            <span class="font-bold text-gray-800">{{ $policy->offer->start_date }}</span>
        </div>
        <div>
            <span class="text-xs text-gray-400 font-bold uppercase block" data-i18n="lblEndDate">End Date</span>
            <span class="font-bold text-gray-800">{{ $policy->offer->end_date }}</span>
        </div>
    </div>

    {{-- Policyholder & Vehicle Details Section --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6 text-sm">
        {{-- Policyholder Details extracted from audit log or user inputs --}}
        <div class="border border-gray-200 p-5 rounded-xl">
            <h2 class="font-black text-gray-900 uppercase text-xs tracking-wider mb-3 border-b pb-2" data-i18n="secPolicyholder">Policyholder Details</h2>
            <div class="space-y-1.5">
                <p><span class="text-gray-500" data-i18n="lblName">Name:</span> <strong class="text-gray-900">{{ $policyholder['lastName'] ?? '-' }} {{ $policyholder['firstName'] ?? '-' }}</strong></p>
                <p><span class="text-gray-500" data-i18n="lblTaxId">Personal ID (CNP):</span> <strong class="font-mono text-gray-900">{{ $policyholder['taxId'] ?? '-' }}</strong></p>
                <p><span class="text-gray-500" data-i18n="lblIdDoc">Identity Doc:</span> <strong>{{ $policyholder['identification']['idType'] ?? 'CI' }} {{ $policyholder['identification']['idNumber'] ?? '-' }}</strong></p>
                <p><span class="text-gray-500" data-i18n="lblEmail">Email:</span> <strong>{{ $policyholder['email'] ?? '-' }}</strong></p>
                <p><span class="text-gray-500" data-i18n="lblAddress">Address:</span> <strong>{{ $policyholder['address']['street'] ?? '-' }} {{ $policyholder['address']['houseNumber'] ?? '' }}, {{ $policyholder['address']['city'] ?? '-' }}, {{ $policyholder['address']['county'] ?? '-' }}</strong></p>
            </div>
        </div>

        {{-- Insured Vehicle technical specifications --}}
        <div class="border border-gray-200 p-5 rounded-xl">
            <h2 class="font-black text-gray-900 uppercase text-xs tracking-wider mb-3 border-b pb-2" data-i18n="secVehicle">Vehicle Details</h2>
            <div class="space-y-1.5">
                <p><span class="text-gray-500" data-i18n="lblBrandModel">Brand & Model:</span> <strong class="text-gray-900">{{ $vehicle['brand'] ?? '-' }} {{ $vehicle['model'] ?? '-' }}</strong></p>
                <p><span class="text-gray-500" data-i18n="lblPlate">License Plate:</span> <strong class="font-mono text-gray-900">{{ $vehicle['licensePlate'] ?? '-' }}</strong></p>
                <p><span class="text-gray-500" data-i18n="lblVin">Chassis No. (VIN):</span> <strong class="font-mono text-gray-900">{{ $vehicle['vin'] ?? '-' }}</strong></p>
                <p><span class="text-gray-500" data-i18n="lblCiv">CIV Series:</span> <strong>{{ $vehicle['identification']['idNumber'] ?? '-' }}</strong></p>
                <p><span class="text-gray-500" data-i18n="lblCapacityPower">Capacity / Power:</span> <strong>{{ $vehicle['engineDisplacement'] ?? '-' }} cc / {{ $vehicle['enginePower'] ?? '-' }} kW</strong></p>
            </div>
        </div>
    </div>

    {{-- Bonus Malus Classification & Charged Premium --}}
    <div class="p-5 bg-blue-50 border border-blue-200 rounded-xl flex justify-between items-center mb-6">
        <div>
            <span class="text-xs uppercase font-bold text-blue-900 block" data-i18n="lblBm">Bonus-Malus Class</span>
            <span class="text-lg font-black text-blue-900">{{ $policy->offer->bonus_malus ?? 'B0' }}</span>
            <span class="text-xs text-blue-700 block mt-0.5">
                <span data-i18n="lblDc">Direct Compensation:</span>
                <strong id="dcStatus">{{ $policy->has_direct_compensation ? 'YES (Included)' : 'NO' }}</strong>
            </span>
        </div>
        <div class="text-right">
            <span class="text-xs uppercase font-bold text-blue-900 block" data-i18n="lblTotalPremium">Total Premium Paid</span>
            <span class="text-2xl sm:text-3xl font-black text-blue-900">{{ number_format($policy->total_amount, 2) }} RON</span>
        </div>
    </div>

    {{-- Legal Notice & Regulatory Compliance Footer --}}
    <div class="text-[11px] text-gray-400 border-t pt-4 space-y-1 text-center">
        <p data-i18n="legalNotice1">Policy generated automatically via Life Is Hard Insurtech Platform API v1.4.1.</p>
        <p data-i18n="legalNotice2">This electronic document is legally valid without signature or stamp according to Romanian law.</p>
    </div>
</div>

<script>
    // Bind server-side boolean attribute to a client-side JavaScript primitive
    const hasDc = {{ $policy->has_direct_compensation ? 'true' : 'false' }};

    // Localization translation dictionary for document printout
    const docTranslations = {
        en: {
            docTitle: "RCA Insurance Policy - {{ $policy->policy_series }} {{ $policy->policy_number }}",
            officialDoc: "Official Online Issued Document",
            btnPrint: "🖨️ Print / Save as PDF",
            heading: "RCA MOTOR THIRD PARTY LIABILITY POLICY",
            subheading: "Registered in the CEDAM National Database",
            lblSeriesNo: "Series & Number",
            lblInsurer: "Insurer",
            lblStartDate: "Start Date",
            lblEndDate: "End Date",
            secPolicyholder: "Policyholder Details",
            lblName: "Name:",
            lblTaxId: "Personal ID (CNP):",
            lblIdDoc: "Identity Doc:",
            lblEmail: "Email:",
            lblAddress: "Address:",
            secVehicle: "Vehicle Details",
            lblBrandModel: "Brand & Model:",
            lblPlate: "License Plate:",
            lblVin: "Chassis No. (VIN):",
            lblCiv: "CIV Series:",
            lblCapacityPower: "Capacity / Power:",
            lblBm: "Bonus-Malus Class",
            lblDc: "Direct Compensation:",
            dcYes: "YES (Included)",
            dcNo: "NO",
            lblTotalPremium: "Total Premium Paid",
            legalNotice1: "Policy generated automatically via Life Is Hard Insurtech Platform API v1.4.1.",
            legalNotice2: "This electronic document is legally valid without signature or stamp according to Romanian law."
        },
        ro: {
            docTitle: "Poliță Asigurare RCA - {{ $policy->policy_series }} {{ $policy->policy_number }}",
            officialDoc: "Document Oficial Emis Online",
            btnPrint: "🖨️ Printează / Salvează ca PDF",
            heading: "POLIȚĂ DE ASIGURARE RCA",
            subheading: "Înregistrată în baza națională CEDAM",
            lblSeriesNo: "Serie & Număr",
            lblInsurer: "Asigurător",
            lblStartDate: "Data Început",
            lblEndDate: "Data Expirare",
            secPolicyholder: "Date Asigurat",
            lblName: "Nume / Prenume:",
            lblTaxId: "CNP:",
            lblIdDoc: "Act Identitate:",
            lblEmail: "Email:",
            lblAddress: "Adresă:",
            secVehicle: "Date Autovehicul",
            lblBrandModel: "Marcă & Model:",
            lblPlate: "Nr. Înmatriculare:",
            lblVin: "Serie Șasiu (VIN):",
            lblCiv: "Serie CIV:",
            lblCapacityPower: "Capacitate / Putere:",
            lblBm: "Clasă Bonus-Malus",
            lblDc: "Decontare Directă:",
            dcYes: "DA (Inclusă)",
            dcNo: "NU",
            lblTotalPremium: "Primă de Asigurare Achitată",
            legalNotice1: "Poliță generată automat prin Life Is Hard Insurtech Platform API v1.4.1.",
            legalNotice2: "Documentul este valabil fără semnătură și ștampilă conform legislației în vigoare din România."
        }
    };

    let currentLang = localStorage.getItem('app_lang') || 'en';

    /**
     * Translates document text labels and toggles active button states
     */
    function setLanguage(lang) {
        currentLang = lang;
        localStorage.setItem('app_lang', lang);

        document.getElementById('btn-ro').className = lang === 'ro'
            ? "px-2.5 py-1 rounded-lg transition bg-white text-blue-600 shadow-sm"
            : "px-2.5 py-1 rounded-lg transition text-gray-600 hover:text-gray-900";
        document.getElementById('btn-en').className = lang === 'en'
            ? "px-2.5 py-1 rounded-lg transition bg-white text-blue-600 shadow-sm"
            : "px-2.5 py-1 rounded-lg transition text-gray-600 hover:text-gray-900";

        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (docTranslations[lang][key]) {
                el.innerText = docTranslations[lang][key];
            }
        });

        // Update direct compensation localized text
        document.getElementById('dcStatus').innerText = hasDc ? docTranslations[lang].dcYes : docTranslations[lang].dcNo;
    }

    // Apply active locale on page load
    document.addEventListener('DOMContentLoaded', () => {
        setLanguage(currentLang);
    });
</script>

</body>
</html>
