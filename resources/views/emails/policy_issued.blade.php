<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmare Emitere Poliță RCA</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #2d3748; background-color: #f7fafc; margin: 0; padding: 25px 15px;">

<!-- Transactional Card Container -->
<div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">

    <!-- Header cu Brand Insurtech -->
    <div style="background-color: #1e40af; padding: 24px; text-align: center; color: #ffffff;">
        <h1 style="margin: 0; font-size: 20px; font-weight: 800;">Life Is Hard Insurtech Platform</h1>
        <p style="margin: 4px 0 0 0; font-size: 13px; color: #bfdbfe;">Confirmare Oficială Înregistrare Asigurare Auto Obligatorie</p>
    </div>

    <!-- Principal body -->
    <div style="padding: 28px;">
        <h2 style="font-size: 17px; color: #1a202c; margin-top: 0;">Polița ta RCA a fost emisă cu succes!</h2>
        <p style="font-size: 14px; color: #4a5568;">
            Îți confirmăm înregistrarea valabilă a poliței auto în sistemul centralizat național <strong>BAAR / CEDAM</strong>.
        </p>

        <!-- Data summation contract  -->
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px;">
            <tbody>
            <tr style="border-bottom: 1px solid #edf2f7;">
                <td style="padding: 8px 0; font-weight: 600; color: #718096;">Serie & Număr:</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 700; font-family: monospace; color: #1a202c;">
                    {{ $policy->policy_series }} {{ $policy->policy_number }}
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #edf2f7;">
                <td style="padding: 8px 0; font-weight: 600; color: #718096;">Asigurător:</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #1a202c;">
                    {{ $policy->offer->insurer ?? 'Asigurător Autorizat' }}
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #edf2f7;">
                <td style="padding: 8px 0; font-weight: 600; color: #718096;">Perioadă Valabilitate:</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 500; color: #1a202c;">
                    {{ $policy->offer->start_date }} &mdash; {{ $policy->offer->end_date }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: 700; color: #1a202c;">Total Primă Achitată:</td>
                <td style="padding: 10px 0; text-align: right; font-weight: 800; font-size: 16px; color: #16a34a;">
                    {{ number_format($policy->total_amount, 2) }} RON
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Bill attachment document -->
        <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px; margin: 18px 0; font-size: 12px; color: #1e40af;">
            📎 <strong>Document Atașat:</strong> Exemplarul original al poliței RCA emis de platformă este atașat la acest mesaj.
        </div>
    </div>

    <!-- Technical & Conformity ASF footer -->
    <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px; text-align: center; font-size: 11px; color: #94a3b8;">
        Generat automat prin API-ul Life Is Hard Broker v1.4.1. Nu trimiteți mesaje directe ca răspuns la acest e-mail tranzacțional.
    </div>
</div>
</body>
</html>
