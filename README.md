# RCA-Broker-Gateway 📝
Welcome to **RCA Broker Gateway**, a web app built with Laravel, MySQL, and Tailwind CSS designed to make finding and buying Romanian mandatory car insurance (RCA) simple and fast. It connect directly to insurance broker REST APIs in line with ASF and BAAR regulations, helping drivers compare live offers and get their policy on the spot, while giving admins a clear view of underlying API traffic.

## Description 📑
RCA Broker Gateway guides users through an easy, end-to-end flow: enter car and driver details, check real-time quotes, apply Bonus-Malus ratings, toggle direct settlement, and issue the policy document right away. The app features a clean **User Dashboard** to keep track of active and past policies, an **Admit Audit Trail** protected by authentication to inspect incoming and outgoing API calls with their raw HTTP status codes, and an **AI Insurance Assistant** ready to explain Romanian traffic rules and policy questions in plain Romanian or English.
## Screenshots 📷

### Form page and AI Chatbot
![Calculator RCA](screenshots/First_page_SPA.png)

### Offers and AI Chatbot functionality
![Offers RCA](screenshots/Second_page_RCA.png)

### Policy bought
![Bought policy](screenshots/Third_page_RCA.png)

### PDF Format for policy
![Policy in PDF Format](screenshots/Fourth_page_RCA.png)

### History log for policy of a certain user
![History Log](screenshots/Fifth_page_RCA.png)

### Audit trail log for ASF regulations and administrative control
![Audit log trail](screenshots/Sixth_page_RCA.png)

## Features ⭐

* **Multi-step Form:** An asynchronous Single Page Application style wizard that collects the users information, official administrative code (SIRUTA), and vehicle specifications without full-page reloads.
* **Quote Engine:** Fetches live insurance rates from broker REST endpoints and displays the price comparasion, including Bonus-Malus rating classes and exact validity dates.
* **Direct Compensation Add-on:** A switch for the client to recalculate its offer and update the total payable premium when selecting optional direct claim handling.
* **Policy Issuance and Validation:** A secure transaction handler that verifies active policies, prevents duplicates of policies with the same VIN, and commits finalized contracts to customer profile.
* **Print and PDF Generation:** A dedicated print-ready Blade template that automatically strips UI buttons via CSS (@media print), generating clean, legally compliant policy documents for saving or printing.
* **Automated Email Dispatch:** Event-driven notification pipeline seding policy details and HTML certificate attachments immediatly upon issuance.
* **Admin Audit Trail:** Secured dashboard for monitoring; utilizing HTTP Basic Authentication to inspect paginated request/response payloads, correlation IDs, and external API latency.
* **Customer Dashboard:** Authenticated workspace for users allowing them to review their policy portofolio, verify validity dates, and re-download official documentation anytime.
* **Algorithmic CNP Parser:** Validation utility for the client that decodes birthdates and biological gender directly from the Romanian 13-digit Personal Identification Number via regex.
* **Bilingual Localization (RO/EN):** Instant in-browser translation between Romanian and English using vanilla JavaScript, preserving the user's language choice in localstorage across visits.
* **Layered Security Architecture:** Standardized session authentication with Bcrypt password hashing, signed verification URLs, CSRF token validation on AJAX requests, and rate-limiting middleware.
* **Embbeded AI RCA Assistant:** Chatbot integrated with Google Gemini API providing instant, context-aware advice on ASF regulations, Bonus-Malus adjustments, and claim resolution steps.

