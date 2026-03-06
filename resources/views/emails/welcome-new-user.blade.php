<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dobrodošli na Pausalci.com</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 40px 30px; border-radius: 10px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2563eb; margin-bottom: 10px; font-size: 28px;">🎉 Dobrodošli na Pausalci.com!</h1>
            <p style="font-size: 16px; color: #6b7280;">
                Automatski smo kreirali besplatan nalog za vas
            </p>
        </div>

        <div style="background-color: #fff; padding: 25px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #2563eb;">
            <p style="font-size: 16px; margin-bottom: 15px;">
                Pozdrav <strong>{{ $user->name }}</strong>,
            </p>
            <p style="font-size: 16px; margin-bottom: 15px;">
                PDF faktura stiže na email za par sekundi.
            </p>
            <p style="font-size: 16px; margin-bottom: 20px;">
                Da biste mogli da upravljate svojim fakturama i koristite sve funkcije, potrebno je samo da postavite šifru:
            </p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}" style="display: inline-block; background-color: #2563eb; color: #fff; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);">
                    Postavite šifru
                </a>
            </div>
            <p style="font-size: 14px; color: #6b7280; margin-top: 20px;">
                Ako dugme ne radi, kopirajte ovaj link u browser:<br>
                <a href="{{ $resetUrl }}" style="color: #2563eb; word-break: break-all;">{{ $resetUrl }}</a>
            </p>
        </div>

        <div style="background-color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
            <h2 style="color: #1f2937; font-size: 18px; margin-bottom: 15px;">✨ Šta dobijate sa besplatnim nalogom:</h2>
            <ul style="padding-left: 20px; margin-bottom: 0;">
                <li style="margin-bottom: 10px;"><strong>3 fakture mesečno</strong> - Dovoljno za male potrebe</li>
                <li style="margin-bottom: 10px;"><strong>PDF generisanje</strong> - Profesionalne fakture u sekundi</li>
                <li style="margin-bottom: 10px;"><strong>Čuvanje klijenata</strong> - Svi podaci na jednom mestu</li>
                <li style="margin-bottom: 10px;"><strong>Osnovni izveštaji</strong> - Uvid u vaše poslovanje</li>
            </ul>
        </div>

        <div style="background-color: #fef3c7; padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #f59e0b;">
            <h2 style="color: #92400e; font-size: 18px; margin-bottom: 10px;">🚀 Premium plan - Neograničene mogućnosti</h2>
            <p style="margin-bottom: 15px; color: #78350f;">
                Kada vam zatreba više, nadogradite na premium:
            </p>
            <ul style="padding-left: 20px; margin-bottom: 15px; color: #78350f;">
                <li style="margin-bottom: 8px;">✓ <strong>Neograničen broj faktura</strong></li>
                <li style="margin-bottom: 8px;">✓ <strong>Automatska eFaktura</strong> integracija</li>
                <li style="margin-bottom: 8px;">✓ <strong>Praćenje plaćanja</strong> i podsetnici</li>
                <li style="margin-bottom: 8px;">✓ <strong>Napredni izveštaji</strong> za poresku</li>
                <li style="margin-bottom: 8px;">✓ <strong>Prioritetna podrška</strong></li>
            </ul>
            <a href="{{ config('app.url') }}/pricing" style="display: inline-block; background-color: #f59e0b; color: #fff; padding: 10px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Pogledajte cene
            </a>
        </div>

        <div style="background-color: #e0f2fe; padding: 15px; border-radius: 8px; margin-bottom: 25px; text-align: center;">
            <p style="font-size: 14px; color: #0c4a6e; margin: 0;">
                💡 <strong>Savет:</strong> Prijavite se odmah i istražite sve funkcije aplikacije!
            </p>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb; text-align: center; font-size: 12px; color: #9ca3af;">
            <p style="margin-bottom: 10px; color: #6b7280;">
                Ako niste zatražili ovaj nalog, možete slobodno da ignorišete ovaj email.
            </p>
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
