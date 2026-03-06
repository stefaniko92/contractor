<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova poruka sa kontakt forme</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 10px; border-left: 4px solid #2563eb;">
        <h1 style="color: #2563eb; margin-bottom: 20px; font-size: 24px;">📧 Nova poruka sa kontakt forme</h1>

        <div style="background-color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <table style="width: 100%; font-size: 14px;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 100px;">Od:</td>
                    <td style="padding: 8px 0;">{{ $senderName }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Email:</td>
                    <td style="padding: 8px 0;">
                        <a href="mailto:{{ $senderEmail }}" style="color: #2563eb; text-decoration: none;">
                            {{ $senderEmail }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Naslov:</td>
                    <td style="padding: 8px 0;">{{ $messageSubject }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Vreme:</td>
                    <td style="padding: 8px 0;">{{ now()->format('d.m.Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <div style="background-color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h2 style="color: #1f2937; font-size: 16px; margin-bottom: 15px; margin-top: 0;">Poruka:</h2>
            <div style="white-space: pre-wrap; word-wrap: break-word; color: #4b5563; line-height: 1.6;">{{ $messageBody }}</div>
        </div>

        <div style="background-color: #e0f2fe; padding: 15px; border-radius: 8px; margin-top: 20px;">
            <p style="margin: 0; font-size: 13px; color: #0c4a6e;">
                💡 <strong>Brzi odgovor:</strong> Kliknite na email adresu pošiljaoca ili koristite "Reply" dugme da odgovorite direktno.
            </p>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb; text-align: center; font-size: 12px; color: #9ca3af;">
            <p style="margin: 0;">
                Ova poruka je poslata sa kontakt forme na <strong>Pausalci.com</strong>
            </p>
        </div>
    </div>
</body>
</html>
