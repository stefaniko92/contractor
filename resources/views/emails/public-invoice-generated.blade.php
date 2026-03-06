<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaša faktura je spremna</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 10px;">
        <h1 style="color: #2563eb; margin-bottom: 20px;">Vaša faktura je spremna!</h1>

        <p style="font-size: 16px; margin-bottom: 15px;">
            Pozdrav,
        </p>

        <p style="font-size: 16px; margin-bottom: 15px;">
            Uspešno ste kreirali fakturu <strong>{{ $invoice->invoice_number }}</strong> putem našeg besplatnog generatora.
        </p>

        <p style="font-size: 16px; margin-bottom: 20px;">
            Faktura je u prilogu ovog emaila u PDF formatu.
        </p>

        <div style="border-top: 2px solid #e5e7eb; padding-top: 20px; margin-top: 30px;">
            <p style="font-size: 14px; color: #6b7280; margin-bottom: 10px;">
                <strong>Detalji fakture:</strong>
            </p>
            <table style="width: 100%; font-size: 14px; color: #6b7280;">
                <tr>
                    <td style="padding: 5px 0;"><strong>Broj fakture:</strong></td>
                    <td style="padding: 5px 0;">{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0;"><strong>Datum izdavanja:</strong></td>
                    <td style="padding: 5px 0;">{{ $invoice->issue_date->format('d.m.Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0;"><strong>Datum dospeća:</strong></td>
                    <td style="padding: 5px 0;">{{ $invoice->due_date->format('d.m.Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0;"><strong>Iznos:</strong></td>
                    <td style="padding: 5px 0;">{{ number_format($invoice->amount, 2, ',', '.') }} {{ $invoice->currency }}</td>
                </tr>
            </table>
        </div>

        <div style="background-color: #e0f2fe; padding: 20px; border-radius: 8px; margin-top: 20px;">
            <h2 style="color: #0369a1; font-size: 18px; margin-bottom: 10px;">💡 Neograničeno faktura sa Pausalci.com</h2>
            <p style="margin-bottom: 15px;">
                Besplatni plan omogućava <strong>3 fakture mesečno</strong>. Sa premium planom dobijate:
            </p>
            <ul style="margin-bottom: 15px; padding-left: 20px;">
                <li>Neograničen broj faktura</li>
                <li>Automatsko slanje putem eFakture</li>
                <li>Praćenje prihoda i obaveza</li>
                <li>Izveštaji za poresku</li>
            </ul>
            <a href="{{ config('app.url') }}/pricing" style="display: inline-block; background-color: #0369a1; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 10px;">
                Pogledajte planove
            </a>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb; text-align: center; font-size: 12px; color: #9ca3af;">
            <p style="margin-bottom: 5px;">
                <strong>Pausalci.com</strong> - Sistem za upravljanje paušalnim preduzetnicima
            </p>
            <p style="margin-bottom: 5px;">
                Imate pitanja? Kontaktirajte nas na <a href="mailto:podrska@pausalci.com" style="color: #2563eb;">podrska@pausalci.com</a>
            </p>
            <p>
                <a href="{{ config('app.url') }}" style="color: #2563eb; text-decoration: none;">www.pausalci.com</a>
            </p>
        </div>
    </div>
</body>
</html>
