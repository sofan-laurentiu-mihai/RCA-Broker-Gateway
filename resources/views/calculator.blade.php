<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Laravel CSRF security meta token for authenticating asynchronous JavaScript HTTP requests --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RCA Calculator & Issue | Life Is Hard</title>
    {{-- Load Tailwind CSS via CDN for rapid responsive layout prototyping --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Animated dynamic gradient background */
        body {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Subtle entrance transition for step transitions */
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-result { animation: slideInUp 0.4s ease-out forwards; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 sm:p-6 font-sans text-gray-800">

<div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-6 sm:p-10 max-w-3xl w-full my-8 relative">

    {{-- LANGUAGE SWITCHER: Client-side locale toggler (RO / EN) --}}
    <div class="absolute top-6 right-6 flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200 shadow-sm text-xs font-bold">
        <button type="button" onclick="setLanguage('ro')" id="btn-ro" class="px-2.5 py-1 rounded-lg transition">🇷🇴 RO</button>
        <button type="button" onclick="setLanguage('en')" id="btn-en" class="px-2.5 py-1 rounded-lg transition bg-white text-blue-600 shadow-sm">🇬🇧 EN</button>
    </div>

    <header class="mb-8 text-center pt-2">
        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider rounded-full">Insurtech Engine v1.4.1</span>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight mt-2" data-i18n="title">RCA Insurance Calculator</h1>
        <p class="text-gray-600 text-sm mt-1" data-i18n="subtitle">Get instant quotes and issue your policy directly online.</p>
    </header>

    {{-- STEP 1: POLICYHOLDER & VEHICLE FORM --}}
    <div id="stepForm" class="space-y-6">
        <form id="rcaForm" onsubmit="calculateOffers(event)" class="space-y-5">

            <!-- Section 1: Validity -->
            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                <h2 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-3" data-i18n="sec1">1. Policy Validity</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblStartDate">Start Date</label>
                        {{-- Defaults to tomorrow to comply with Romanian ASF 24-hour advance issuance rules --}}
                        <input type="date" id="startDate" required value="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblDuration">Duration (Months)</label>
                        <select id="termTime" class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                            <option value="12" data-i18n="opt12m">12 Months (1 Year)</option>
                            <option value="6" data-i18n="opt6m">6 Months</option>
                            <option value="1" data-i18n="opt1m">1 Month</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Policyholder Details -->
            <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-200/80">
                <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3" data-i18n="sec2">2. Policyholder Details (Individual)</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblLastName">Last Name</label>
                        <input type="text" id="lastName" required value="Popescu"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblFirstName">First Name</label>
                        <input type="text" id="firstName" required value="Ion"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblTaxId">Personal ID (CNP)</label>
                        {{-- Input triggers automatic birthdate and gender deduction via oninput handler --}}
                        <input type="text" id="taxId" maxlength="13" minlength="13" required value="1900101123456" oninput="validateAndAutofillCNP(this.value)"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm font-mono outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblBirthdate">Date of Birth</label>
                        <input type="date" id="birthdate" required value="1990-01-01"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblGender">Gender</label>
                        <select id="gender" class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                            <option value="m" data-i18n="optMale">Male</option>
                            <option value="f" data-i18n="optFemale">Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblIdNumber">ID Series/No</label>
                        <input type="text" id="idNumber" required value="RD123456"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblPhone">Phone Number</label>
                        <input type="text" id="mobileNumber" required value="0712345678"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblEmail">Email</label>
                        <input type="email" id="email" required value="ion.popescu@example.com"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblCounty">County</label>
                        {{-- Populated dynamically via JavaScript SIRUTA dictionary --}}
                        <select id="countySelect" onchange="onCountyChange(this.value)" required class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-400">
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblCity">City / Locality</label>
                        {{-- Populated dynamically based on selected county --}}
                        <select id="citySelect" onchange="onCityChange(this)" required class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-400">
                        </select>
                    </div>
                </div>

                {{-- Hidden input controls preserving exact SIRUTA codes expected by LIH API --}}
                <input type="hidden" id="county" value="Bucuresti">
                <input type="hidden" id="city" value="Sector 1">
                <input type="hidden" id="cityCode" value="179132">

                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblStreet">Street</label>
                        <input type="text" id="street" required value="Victoriei"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblHouseNumber">Number</label>
                        <input type="text" id="houseNumber" required value="10"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                </div>
            </div>

            <!-- Section 3: Vehicle Details -->
            <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-200/80">
                <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3" data-i18n="sec3">3. Vehicle Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblRegType">Registration Status</label>
                        <select id="registrationType" class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                            <option value="registered" data-i18n="optRegRO">Registered in RO</option>
                            <option value="temporaryRegistered" data-i18n="optRegTemp">Temporary Plates (Red)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblPlate">License Plate</label>
                        <input type="text" id="licensePlate" value="B123ABC"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm font-mono uppercase outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblVin">Chassis Number (VIN)</label>
                        <input type="text" id="vin" required value="WAUZZZ8K9BA123456"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm font-mono uppercase outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblCiv">CIV Series</label>
                        <input type="text" id="civNumber" required value="A1234567"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblBrand">Brand</label>
                        <input type="text" id="brand" required value="AUDI"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblModel">Model</label>
                        <input type="text" id="model" required value="A4"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblYear">Year of Manufacture</label>
                        <input type="number" id="year" required value="2018"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblDisplacement">Engine Capacity (cc)</label>
                        <input type="number" id="displacement" required value="1968"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblPower">Power (kW)</label>
                        <input type="number" id="power" required value="110"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblWeight">Total Weight (kg)</label>
                        <input type="number" id="weight" required value="2050"
                               class="w-full border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1" data-i18n="lblSeatsFuel">Seats / Fuel Type</label>
                        <div class="flex gap-1">
                            <input type="number" id="seats" required value="5" class="w-1/3 border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                            <select id="fuelType" class="w-2/3 border border-gray-200 bg-white rounded-lg p-2.5 text-sm outline-none">
                                <option value="motorina" data-i18n="fuelDiesel">Diesel</option>
                                <option value="benzina" data-i18n="fuelPetrol">Petrol</option>
                                <option value="hibrid" data-i18n="fuelHybrid">Hybrid</option>
                                <option value="electric" data-i18n="fuelElectric">Electric</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTONS: CLEAR & SUBMIT -->
            <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                <!-- Clear Button -->
                <button type="button"
                        onclick="clearRcaForm()"
                        class="w-full sm:w-auto px-5 py-4 rounded-xl border border-gray-200 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm transition flex items-center justify-center gap-2 hover:text-red-600 hover:border-red-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span data-i18n="btnClear">Clear Form</span>
                </button>

                <!-- Submit Button -->
                <button type="submit"
                        id="submitBtn"
                        class="w-full sm:flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition shadow-lg active:scale-[0.99] flex items-center justify-center gap-2">
                    <span data-i18n="btnCalculate">Calculate RCA Quotes</span>
                    <span id="btnLoader" class="hidden animate-spin">⏳</span>
                </button>
            </div>
        </form>
    </div>

    {{-- STEP 2: OFFERS COMPARISON LIST --}}
    <div id="stepOffers" class="hidden space-y-5 animate-result">
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <div>
                <h2 class="text-xl font-extrabold text-gray-900" data-i18n="offersHeading">Available Quotes</h2>
                <p class="text-xs text-gray-500" data-i18n="offersSubheading">Choose your preferred insurer and configure direct compensation</p>
            </div>
            <button onclick="backToForm()" class="text-xs text-blue-600 underline font-semibold hover:text-blue-800" data-i18n="btnEdit">
                &larr; Edit Details
            </button>
        </div>

        {{-- Destination target populated dynamically via renderOffers() --}}
        <div id="offersList" class="space-y-4"></div>
    </div>

    {{-- STEP 3: SUCCESS CONFIRMATION & PDF DOWNLOAD --}}
    <div id="stepSuccess" class="hidden animate-result">
        <div class="border border-green-200 rounded-2xl p-6 bg-green-50/70 space-y-4 text-center">
            <div class="w-16 h-16 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto text-3xl shadow-lg">
                ✓
            </div>
            <h2 class="text-2xl font-black text-green-950" data-i18n="successTitle">Policy successfully issued!</h2>
            <p class="text-sm text-green-800" data-i18n="successSubtitle">Your RCA contract is active and registered in the CEDAM database.</p>

            <div class="grid grid-cols-2 gap-4 text-left my-4">
                <div class="bg-white p-4 rounded-xl shadow-sm border border-green-100">
                    <p class="text-xs text-gray-400 font-bold uppercase" data-i18n="lblPolicyNo">Policy Number</p>
                    <p class="text-lg font-mono font-bold text-gray-900 mt-1" id="resPolicyNumber">-</p>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-green-100">
                    <p class="text-xs text-gray-400 font-bold uppercase" data-i18n="lblTotalPaid">Total Paid</p>
                    <p class="text-lg font-bold text-green-700 mt-1" id="resTotalAmount">-</p>
                </div>
            </div>

            <div class="space-y-2 pt-2">
                <a id="downloadPdfBtn" href="#" target="_blank"
                   class="flex items-center justify-center gap-2 w-full py-3.5 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-xl text-sm transition shadow-md active:scale-95" data-i18n="btnDownloadPdf">
                    👁️ View & Download PDF Policy
                </a>

                <button onclick="location.reload()"
                        class="w-full py-3 bg-white hover:bg-gray-100 text-gray-700 font-bold rounded-xl text-sm border border-gray-200 transition shadow-sm active:scale-95" data-i18n="btnNewCalc">
                    🏠 Calculate another policy
                </button>
            </div>
        </div>
    </div>

    {{-- Error Banner: Displays network errors, validation failures, or business rule blocks --}}
    <div id="errorMessage" class="hidden mt-4 bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-xl text-sm animate-result">
        <p id="errorText"></p>
    </div>
</div>

<script>
    // Client-side i18n translation dictionary
    const translations = {
        en: {
            title: "RCA Insurance Calculator",
            subtitle: "Get instant quotes and issue your policy directly online.",
            sec1: "1. Policy Validity",
            lblStartDate: "Start Date",
            lblDuration: "Duration (Months)",
            opt12m: "12 Months (1 Year)",
            opt6m: "6 Months",
            opt1m: "1 Month",
            sec2: "2. Policyholder Details (Individual)",
            lblLastName: "Last Name",
            lblFirstName: "First Name",
            lblTaxId: "Personal ID (CNP)",
            lblBirthdate: "Date of Birth",
            lblGender: "Gender",
            optMale: "Male",
            optFemale: "Female",
            lblIdNumber: "ID Series/No",
            lblPhone: "Phone Number",
            lblEmail: "Email",
            lblCounty: "County",
            lblCity: "City",
            lblCityCode: "City Code (SIRUTA)",
            lblStreet: "Street",
            lblHouseNumber: "Number",
            sec3: "3. Vehicle Details",
            lblRegType: "Registration Status",
            optRegRO: "Registered in RO",
            optRegTemp: "Temporary Plates (Red)",
            lblPlate: "License Plate",
            lblVin: "Chassis Number (VIN)",
            lblCiv: "CIV Series",
            lblBrand: "Brand",
            lblModel: "Model",
            lblYear: "Year of Manufacture",
            lblDisplacement: "Engine Capacity (cc)",
            lblPower: "Power (kW)",
            lblWeight: "Total Weight (kg)",
            lblSeatsFuel: "Seats / Fuel Type",
            fuelDiesel: "Diesel",
            fuelPetrol: "Petrol",
            fuelHybrid: "Hybrid",
            fuelElectric: "Electric",
            btnCalculate: "Calculate RCA Quotes",
            offersHeading: "Available Quotes",
            offersSubheading: "Choose your preferred insurer and configure direct compensation",
            btnEdit: "← Edit Details",
            noOffers: "No quotes were returned.",
            classTag: "Class",
            validityTag: "Validity",
            dcTitle: "Direct Compensation",
            dcSubtitle: "Repair your car using your own RCA in case of an accident",
            lblTotalPrice: "Total Price",
            btnIssue: "Issue Policy →",
            successTitle: "Policy successfully issued!",
            successSubtitle: "Your RCA contract is active and registered in the CEDAM database.",
            lblPolicyNo: "Policy Number",
            lblTotalPaid: "Total Paid",
            btnDownloadPdf: "👁️ View & Download PDF Policy",
            btnNewCalc: "🏠 Calculate another policy",
            errGeneral: "An error occurred while calculating quotes.",
            errNetwork: "Network error or connection refused.",
            errIssue: "Error issuing the policy.",
            btnClear: "Clear Form"
        },
        ro: {
            title: "Calculator & Emitere RCA",
            subtitle: "Obține oferte instante și emite polița direct online.",
            sec1: "1. Valabilitate Poliță",
            lblStartDate: "Data Început",
            lblDuration: "Durată (Luni)",
            opt12m: "12 Luni (1 An)",
            opt6m: "6 Luni",
            opt1m: "1 Lună",
            sec2: "2. Date Asigurat (Persoană Fizică)",
            lblLastName: "Nume",
            lblFirstName: "Prenume",
            lblTaxId: "CNP Asigurat",
            lblBirthdate: "Data Nașterii",
            lblGender: "Gen",
            optMale: "Masculin",
            optFemale: "Feminin",
            lblIdNumber: "Serie/Nr CI",
            lblPhone: "Număr Telefon",
            lblEmail: "Adresă Email",
            lblCounty: "Județ",
            lblCity: "Oraș",
            lblCityCode: "Cod Oraș (SIRUTA)",
            lblStreet: "Stradă",
            lblHouseNumber: "Număr",
            sec3: "3. Date Autovehicul",
            lblRegType: "Înmatriculare",
            optRegRO: "Înmatriculat în RO",
            optRegTemp: "Numere Provizorii (Roșii)",
            lblPlate: "Număr Înmatriculare",
            lblVin: "Serie Șasiu (VIN)",
            lblCiv: "Serie CIV",
            lblBrand: "Marcă",
            lblModel: "Model",
            lblYear: "An Fabricație",
            lblDisplacement: "Cilindree (cmc)",
            lblPower: "Putere (kW)",
            lblWeight: "Masă Totală (kg)",
            lblSeatsFuel: "Locuri / Combustibil",
            fuelDiesel: "Motorină",
            fuelPetrol: "Benzină",
            fuelHybrid: "Hibrid",
            fuelElectric: "Electric",
            btnCalculate: "Calculează Ofertele RCA",
            offersHeading: "Oferte Disponibile",
            offersSubheading: "Alege asigurătorul dorit și configurează decontarea directă",
            btnEdit: "← Modifică Datele",
            noOffers: "Nu a fost returnată nicio ofertă.",
            classTag: "Clasa",
            validityTag: "Valabilitate",
            dcTitle: "Decontare Directă",
            dcSubtitle: "Repari mașina pe propriul RCA în caz de accident",
            lblTotalPrice: "Preț Total",
            btnIssue: "Emite Polița →",
            successTitle: "Polița a fost emisă cu succes!",
            successSubtitle: "Contractul tău RCA este activ și înregistrat în baza de date CEDAM.",
            lblPolicyNo: "Număr Poliță",
            lblTotalPaid: "Total Achitat",
            btnDownloadPdf: "👁️ Vezi & Descarcă Polița PDF",
            btnNewCalc: "🏠 Calculează o altă poliță",
            errGeneral: "A apărut o eroare la calcularea ofertelor.",
            errNetwork: "Eroare de rețea sau conexiune refuzată.",
            errIssue: "Eroare la emiterea poliței.",
            btnClear: "Șterge datele"
        }
    };

    // State management for active language and quotation cache
    let currentLang = localStorage.getItem('app_lang') || 'en';
    let receivedOffers = [];

    /**
     * Updates active UI language, syncs localStorage, and re-renders text nodes
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
            if (translations[lang][key]) {
                el.innerText = translations[lang][key];
            }
        });

        // Re-render offers in real time if quotations are currently visible
        if (receivedOffers.length > 0) {
            renderOffers(receivedOffers);
        }
    }

    /**
     * Submits form payload to backend API to query insurance quotes
     */
    async function calculateOffers(event) {
        event.preventDefault();
        hideError();

        const btn = document.getElementById('submitBtn');
        const loader = document.getElementById('btnLoader');
        btn.disabled = true;
        loader.classList.remove('hidden');

        // Extract and cast input fields into clean payload matching controller validation
        const payload = {
            startDate: document.getElementById('startDate').value,
            termTime: parseInt(document.getElementById('termTime').value),
            lastName: document.getElementById('lastName').value,
            firstName: document.getElementById('firstName').value,
            taxId: document.getElementById('taxId').value,
            birthdate: document.getElementById('birthdate').value,
            gender: document.getElementById('gender').value,
            email: document.getElementById('email').value,
            mobileNumber: document.getElementById('mobileNumber').value,
            idNumber: document.getElementById('idNumber').value,
            county: document.getElementById('county').value,
            city: document.getElementById('city').value,
            cityCode: parseInt(document.getElementById('cityCode').value),
            street: document.getElementById('street').value,
            houseNumber: document.getElementById('houseNumber').value,
            registrationType: document.getElementById('registrationType').value,
            licensePlate: document.getElementById('licensePlate').value,
            vin: document.getElementById('vin').value,
            civNumber: document.getElementById('civNumber').value,
            brand: document.getElementById('brand').value,
            model: document.getElementById('model').value,
            year: parseInt(document.getElementById('year').value),
            displacement: parseInt(document.getElementById('displacement').value),
            power: parseInt(document.getElementById('power').value),
            weight: parseInt(document.getElementById('weight').value),
            seats: parseInt(document.getElementById('seats').value),
            fuelType: document.getElementById('fuelType').value,
        };

        try {
            const res = await fetch('/api/rca/quotes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                return showError(data.message || translations[currentLang].errGeneral);
            }

            receivedOffers = data.offers;
            renderOffers(data.offers);

            // Transition from form view to quotation results step
            document.getElementById('stepForm').classList.add('hidden');
            document.getElementById('stepOffers').classList.remove('hidden');
        } catch (err) {
            showError(translations[currentLang].errNetwork);
        } finally {
            btn.disabled = false;
            loader.classList.add('hidden');
        }
    }

    // Static registry mapping Romanian localities to official administrative SIRUTA postal codes
    const sirutaDatabase = {
        "Bucuresti": [
            { name: "Sector 1", code: 179132 },
            { name: "Sector 2", code: 179141 },
            { name: "Sector 3", code: 179150 },
            { name: "Sector 4", code: 179169 },
            { name: "Sector 5", code: 179178 },
            { name: "Sector 6", code: 179187 }
        ],
        "Cluj": [
            { name: "Cluj-Napoca", code: 54975 },
            { name: "Turda", code: 55259 },
            { name: "Dej", code: 55008 },
            { name: "Floresti", code: 57706 }
        ],
        "Timis": [
            { name: "Timisoara", code: 155243 },
            { name: "Lugoj", code: 155350 },
            { name: "Dumbravita", code: 156797 }
        ],
        "Iasi": [
            { name: "Iasi", code: 95060 },
            { name: "Pascani", code: 95300 },
            { name: "Miroslava", code: 97871 }
        ],
        "Brasov": [
            { name: "Brasov", code: 40198 },
            { name: "Fagaras", code: 40483 },
            { name: "Sacele", code: 40438 }
        ],
        "Constanta": [
            { name: "Constanta", code: 60419 },
            { name: "Mangalia", code: 60482 },
            { name: "Medgidia", code: 60543 }
        ]
    };

    /**
     * Initializes county dropdown and sets default city selection
     */
    function initLocationSelectors() {
        const countySelect = document.getElementById('countySelect');
        countySelect.innerHTML = Object.keys(sirutaDatabase).map(county =>
            `<option value="${county}" ${county === 'Bucuresti' ? 'selected' : ''}>${county}</option>`
        ).join('');

        onCountyChange('Bucuresti');
    }

    /**
     * Cascades city options when selected county changes
     */
    function onCountyChange(selectedCounty) {
        document.getElementById('county').value = selectedCounty;
        const citySelect = document.getElementById('citySelect');
        const cities = sirutaDatabase[selectedCounty] || [];

        citySelect.innerHTML = cities.map((c, idx) =>
            `<option value="${c.name}" data-code="${c.code}" ${idx === 0 ? 'selected' : ''}>${c.name}</option>`
        ).join('');

        if (cities.length > 0) {
            document.getElementById('city').value = cities[0].name;
            document.getElementById('cityCode').value = cities[0].code;
        }
    }

    /**
     * Synchronizes hidden form values with chosen city name and SIRUTA code
     */
    function onCityChange(selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        document.getElementById('city').value = selectedOption.value;
        document.getElementById('cityCode').value = selectedOption.getAttribute('data-code');
    }

    /**
     * Decodes birth date and gender from Romanian 13-digit Personal Identification Number (CNP)
     */
    function validateAndAutofillCNP(cnp) {
        // Stop execution if string does not strictly match the 13-digit Romanian CNP pattern
        if (!/^[1-8]\d{12}$/.test(cnp)) return;

        const firstDigit = parseInt(cnp[0]);
        let century = 1900;
        let gender = 'm';

        // Parse birth century and biological sex from the leading CNP digit
        if ([1, 2].includes(firstDigit)) {
            century = 1900;
            gender = firstDigit === 1 ? 'm' : 'f';
        } else if ([5, 6].includes(firstDigit)) {
            century = 2000;
            gender = firstDigit === 5 ? 'm' : 'f';
        } else if ([3, 4].includes(firstDigit)) {
            century = 1800;
            gender = firstDigit === 3 ? 'm' : 'f';
        }

        const year = century + parseInt(cnp.substr(1, 2));
        const month = cnp.substr(3, 2);
        const day = cnp.substr(5, 2);

        document.getElementById('birthdate').value = `${year}-${month}-${day}`;
        document.getElementById('gender').value = gender;
    }

    /**
     * Renders received insurance quotes dynamically into HTML card elements
     */
    function renderOffers(offers) {
        const container = document.getElementById('offersContainer') || document.getElementById('offersList');
        const lang = currentLang;

        if (!container) return;

        if (!offers || offers.length === 0) {
            container.innerHTML = `<div class="p-6 bg-red-50 text-red-600 rounded-2xl text-center font-bold">${translations[lang].noOffers}</div>`;
            return;
        }

        container.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                ${offers.map(offer => {
            const initials = offer.insurer ? offer.insurer.substring(0, 2).toUpperCase() : 'RC';
            const hasDcOption = offer.direct_compensation_amount && offer.direct_compensation_amount > offer.premium_amount;
            const dcPrice = hasDcOption ? offer.direct_compensation_amount : offer.premium_amount;

            return `
                    <div class="bg-white border border-gray-200 hover:border-blue-400 rounded-3xl p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center font-black text-lg shadow-sm">
                                        ${initials}
                                    </div>
                                    <div>
                                        <h3 class="font-black text-gray-900 text-base sm:text-lg leading-tight">${offer.insurer}</h3>
                                        <span class="inline-block bg-green-100 text-green-800 text-[11px] font-extrabold px-2 py-0.5 rounded-full mt-1">
                                            ${translations[lang].classTag} ${offer.bonus_malus || 'B0'}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right text-xs text-gray-400">
                                    <span class="block uppercase font-bold text-[10px] tracking-wider">${translations[lang].validityTag}</span>
                                    <span class="font-medium text-gray-600 font-mono">${offer.start_date || '-'}</span>
                                    <span class="block font-medium text-gray-600 font-mono">${offer.end_date || '-'}</span>
                                </div>
                            </div>

                            {{-- Direct Compensation toggle switch --}}
            <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 mb-5 flex items-center justify-between gap-3">
                <div>
                    <span class="text-xs font-bold text-gray-800 block">${translations[lang].dcTitle}</span>
                                    <span class="text-[11px] text-gray-500">${translations[lang].dcSubtitle}</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox"
                                           id="dc_toggle_${offer.id}"
                                           onchange="togglePrice(${offer.id}, ${offer.premium_amount}, ${dcPrice})"
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>

                        {{-- Total calculated price and emission dispatch button --}}
            <div class="bg-gray-900 text-white rounded-2xl p-4 flex items-center justify-between gap-3">
                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">${translations[lang].lblTotalPrice}</span>
                                <span id="price_display_${offer.id}" class="text-xl sm:text-2xl font-black text-white">
                                    ${parseFloat(offer.premium_amount).toFixed(2)} RON
                                </span>
                            </div>
                            <button type="button"
                                    onclick="issuePolicy(${offer.id})"
                                    class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs sm:text-sm font-extrabold px-4 py-2.5 rounded-xl transition shadow active:scale-95 flex items-center gap-1.5 whitespace-nowrap">
                                <span>${translations[lang].btnIssue}</span>
                            </button>
                        </div>
                    </div>
                    `;
        }).join('')}
            </div>
        `;
    }

    /**
     * Swaps displayed card price when Direct Compensation checkbox is checked/unchecked
     */
    function togglePrice(offerId, standardPrice, directPrice) {
        const isChecked = document.getElementById(`dc_toggle_${offerId}`).checked;
        const display = document.getElementById(`price_display_${offerId}`);
        const selectedPrice = isChecked ? directPrice : standardPrice;

        display.innerText = `${parseFloat(selectedPrice).toFixed(2)} RON`;
    }

    /**
     * Sends issuance request to backend and shows confirmation screen
     */
    async function issuePolicy(offerDbId) {
        hideError();
        const toggle = document.getElementById(`dc_toggle_${offerDbId}`);
        const hasDirectComp = toggle ? toggle.checked : false;

        try {
            const res = await fetch('/api/rca/issue', {
                method: 'POST',
                // 'same-origin' credentials ensures the session cookie is transmitted so Auth::check() resolves
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    offer_db_id: offerDbId,
                    direct_compensation: hasDirectComp
                })
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                return showError(data.message || translations[currentLang].errIssue);
            }

            document.getElementById('resPolicyNumber').innerText = `${data.policy.policy_series} ${data.policy.policy_number}`;
            document.getElementById('resTotalAmount').innerText = `${parseFloat(data.policy.total_amount).toFixed(2)} RON`;

            // Bind generated policy ID to internal document rendering endpoint
            document.getElementById('downloadPdfBtn').href = `/policy/${data.policy.id}/view-pdf`;

            document.getElementById('stepOffers').classList.add('hidden');
            document.getElementById('stepSuccess').classList.remove('hidden');

        } catch (err) {
            showError(translations[currentLang].errIssue);
        }
    }

    function backToForm() {
        document.getElementById('stepOffers').classList.add('hidden');
        document.getElementById('stepForm').classList.remove('hidden');
        hideError();
    }

    function showError(msg) {
        document.getElementById('errorText').innerText = msg;
        document.getElementById('errorMessage').classList.remove('hidden');
    }

    function hideError() {
        document.getElementById('errorMessage').classList.add('hidden');
    }

    // Initialize dropdowns and active language when DOM content is fully loaded
    document.addEventListener('DOMContentLoaded', () => {
        initLocationSelectors();
        setLanguage(currentLang);
    });
</script>

{{-- FLOATING AI CHAT WIDGET --}}
<div id="chatWidget" class="fixed bottom-6 right-6 z-50">
    <button id="chatToggleBtn" onclick="toggleChat()" class="bg-blue-600 hover:bg-blue-700 text-white w-14 h-14 rounded-full shadow-2xl flex items-center justify-center text-2xl transition transform hover:scale-110 active:scale-95">
        💬
    </button>

    <div id="chatWindow" class="hidden absolute bottom-16 right-0 w-[340px] sm:w-[380px] bg-white rounded-3xl shadow-2xl border border-gray-100 flex flex-col overflow-hidden animate-result">
        <div class="bg-gray-900 text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center font-bold text-sm">
                    🤖
                </div>
                <div>
                    <h3 class="font-bold text-sm leading-none">Asistent AI RCA</h3>
                    <span class="text-[10px] text-green-400 font-medium">● Online / Consultanță</span>
                </div>
            </div>
            <button onclick="toggleChat()" class="text-gray-400 hover:text-white text-lg font-bold">✕</button>
        </div>

        <div id="chatMessages" class="p-4 h-80 overflow-y-auto space-y-3 bg-gray-50/50 text-xs">
            <div class="bg-white border border-gray-100 p-3 rounded-2xl text-gray-700 shadow-sm max-w-[85%]">
                Bună! Sunt asistentul tău inteligent. Cu ce informații despre polița RCA sau legislație te pot ajuta?
            </div>
        </div>

        <div class="p-2 bg-white border-t border-gray-100 flex gap-1.5 overflow-x-auto text-[11px] whitespace-nowrap">
            <button onclick="sendQuickMessage('Ce este decontarea directă?')" class="bg-blue-50 text-blue-700 hover:bg-blue-100 px-2.5 py-1 rounded-full font-semibold transition">Ce e decontarea directă?</button>
            <button onclick="sendQuickMessage('Cum aflu clasa Bonus-Malus?')" class="bg-blue-50 text-blue-700 hover:bg-blue-100 px-2.5 py-1 rounded-full font-semibold transition">Bonus-Malus?</button>
        </div>

        <form id="chatForm" onsubmit="handleChatSubmit(event)" class="p-3 bg-white border-t border-gray-100 flex gap-2 items-center">
            <input type="text" id="chatInput" placeholder="Scrie o întrebare despre RCA..." class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-400">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition">
                Trimite
            </button>
        </form>
    </div>
</div>

<script>
    let chatHistory = [];

    function toggleChat() {
        const windowElem = document.getElementById('chatWindow');
        windowElem.classList.toggle('hidden');
        if (!windowElem.classList.contains('hidden')) {
            document.getElementById('chatInput').focus();
        }
    }

    /**
     * Resets all form fields and repositions focus on the first input
     */
    function clearRcaForm() {
        const form = document.getElementById('rcaForm');
        if (!form) return;

        form.querySelectorAll('input:not([type="hidden"])').forEach(input => {
            input.value = '';
        });

        form.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
        });

        initLocationSelectors();
        hideError();

        const firstInput = document.getElementById('lastName');
        if (firstInput) {
            firstInput.focus();
        }
    }

    function sendQuickMessage(text) {
        document.getElementById('chatInput').value = text;
        handleChatSubmit(new Event('submit'));
    }

    /**
     * Dispatches user prompt to backend Gemini AI chat route and appends response
     */
    async function handleChatSubmit(e) {
        e.preventDefault();
        const input = document.getElementById('chatInput');
        const text = input.value.trim();
        if (!text) return;

        const container = document.getElementById('chatMessages');

        // Append user prompt bubble
        container.innerHTML += `
            <div class="flex justify-end">
                <div class="bg-blue-600 text-white p-3 rounded-2xl shadow-sm max-w-[85%]">
                    ${text}
                </div>
            </div>
        `;
        input.value = '';
        container.scrollTop = container.scrollHeight;

        // Render loading state bubble
        const loaderId = 'loader_' + Date.now();
        container.innerHTML += `
            <div id="${loaderId}" class="flex justify-start">
                <div class="bg-white border border-gray-100 p-3 rounded-2xl text-gray-400 shadow-sm animate-pulse">
                    AI-ul scrie... ✍️
                </div>
            </div>
        `;
        container.scrollTop = container.scrollHeight;

        try {
            const res = await fetch('/api/chat/ask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message: text,
                    history: chatHistory
                })
            });

            const data = await res.json();
            document.getElementById(loaderId)?.remove();

            const reply = data.reply || 'Ne cerem scuze, a apărut o problemă.';
            container.innerHTML += `
                <div class="flex justify-start">
                    <div class="bg-white border border-gray-100 p-3 rounded-2xl text-gray-700 shadow-sm max-w-[85%] leading-relaxed">
                        ${reply}
                    </div>
                </div>
            `;
            container.scrollTop = container.scrollHeight;

            // Maintain conversation context in memory
            chatHistory.push({ sender: 'user', text: text });
            chatHistory.push({ sender: 'assistant', text: reply });

        } catch (err) {
            document.getElementById(loaderId)?.remove();
            container.innerHTML += `
                <div class="text-red-500 text-[11px] text-center p-1">Eroare de conexiune cu asistentul.</div>
            `;
        }
    }
</script>

</body>
</html>
