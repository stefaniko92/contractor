# Dual Email Flow - Dokumentacija

## Pregled

Sistem sada šalje **dva odvojena emaila** za bolje korisničko iskustvo:

### 1. Email sa Fakturom (Brz)
- Šalje se **odmah** sa PDF prilogom
- Nema dodatnih informacija osim fakture
- Fokus: Brza isporuka PDF-a

### 2. Welcome Email (Samo za nove korisnike)
- Šalje se **odmah posle** prvog emaila
- Sadrži link za postavljanje šifre
- Objašnjava benefite naloga
- Podstiče na registraciju

## Implementacija

### Izmenjeni Fajlovi

#### 1. `app/Mail/PublicInvoiceGenerated.php`
**Izmene:**
- Uklonjen `$isNewUser` parametar
- Uklonjen `$resetToken` parametar
- Pojednostavljen konstruktor
- Email sada sadrži samo fakturu

#### 2. `resources/views/emails/public-invoice-generated.blade.php`
**Izmene:**
- Uklonjeno `@if($isNewUser && $resetUrl)` sekcija
- Uklonjeno prikazivanje aktivacionog linka
- Fokus samo na detalje fakture i marketing
- Dodato 💡 emoji za premium sekciju

#### 3. NOVI: `app/Mail/WelcomeNewUser.php`
**Funkcija:**
- Poseban mailable za dobrodošlicu
- Prima `User` i `resetToken`
- Generiše reset URL
- Šalje na `emails.welcome-new-user` template

#### 4. NOVI: `resources/views/emails/welcome-new-user.blade.php`
**Sadržaj:**
- 🎉 Dobrodošlica naslov
- "PDF faktura stiže za par sekundi"
- Veliki CTA dugme "Postavite šifru"
- Lista benefita besplatnog naloga:
  - ✓ 3 fakture mesečno
  - ✓ PDF generisanje
  - ✓ Čuvanje klijenata
  - ✓ Osnovni izveštaji
- Žuta sekcija sa premium features:
  - Neograničene fakture
  - eFaktura integracija
  - Praćenje plaćanja
  - Napredni izveštaji
- Napomena da mogu ignorisati email

#### 5. `app/Services/PublicInvoiceService.php`
**Izmene:**
```php
private function sendEmail(User $user, Invoice $invoice, string $pdfPath, bool $isNewUser): void
{
    // Uvek šalji fakturu odmah
    Mail::to($user->email)->send(
        new \App\Mail\PublicInvoiceGenerated($invoice, $pdfPath)
    );

    // Za nove korisnike šalji welcome email
    if ($isNewUser) {
        $resetToken = Password::createToken($user);
        Mail::to($user->email)->send(
            new \App\Mail\WelcomeNewUser($user, $resetToken)
        );
    }
}
```

## User Journey

### Za Novog Korisnika

1. **Unos podataka na frontend formi**
   - Email: `novi@korisnik.com`
   - PIB, podaci o prodavcu/kupcu, stavke

2. **API Request**
   ```
   POST /api/public/generate-invoice
   ```

3. **Backend Processing** (~2-3 sekunde)
   - Kreiranje User + UserCompany
   - Kreiranje Client
   - Kreiranje Invoice + InvoiceItems
   - PDF generisanje (Gotenberg)

4. **Email #1: Faktura** (odmah)
   ```
   To: novi@korisnik.com
   Subject: Vaša faktura je spremna - Pausalci.com
   Attachment: Faktura-1-2025.pdf
   ```

5. **Email #2: Welcome** (odmah posle)
   ```
   To: novi@korisnik.com
   Subject: Dobrodošli na Pausalci.com - Postavite šifru
   CTA: "Postavite šifru" dugme
   ```

6. **Korisnik dobija oba emaila u inbox-u**
   - Vidi fakturu u prvom emailu
   - Vidi poziv za registraciju u drugom

### Za Postojećeg Korisnika

1. **Unos podataka na frontendu**
   - Email: `postojeci@korisnik.com` (već registrovan)

2. **API Request**
   ```
   POST /api/public/generate-invoice
   ```

3. **Backend Processing**
   - Pronalazi postojećeg User
   - Kreiranje Client
   - Kreiranje Invoice + InvoiceItems
   - PDF generisanje

4. **Email #1: Faktura** (jedini email)
   ```
   To: postojeci@korisnik.com
   Subject: Vaša faktura je spremna - Pausalci.com
   Attachment: Faktura-2-2025.pdf
   ```

5. **NE ŠALJE SE welcome email** (već ima nalog)

## Prednosti Ovog Pristupa

### ✅ Performanse
- PDF email se šalje bez čekanja na dodatnu logiku
- Welcome email ne blokira PDF delivery
- Mogućnost da se welcome email stavi u queue

### ✅ User Experience
- Korisnik brže dobija fakturu
- Welcome email je fokusiraniji i elegantniji
- Jasno razdvojene informacije
- Manje zbunjujući flow

### ✅ Marketing
- Welcome email ima više prostora za marketing
- Bolje highlightovanje premium features
- Jasnije objašnjenje benefita besplatnog naloga
- Veća verovatnoća konverzije

### ✅ Email Deliverability
- Manji emailovi = bolja deliverability
- PDF attachment ne utiče na welcome email
- Dva manja emaila > jedan veliki

## Testing

### Testiranje Welcome Emaila

```php
// U testu možete proveriti oba emaila
Mail::assertSent(PublicInvoiceGenerated::class, function ($mail) {
    return $mail->hasTo('test@example.com');
});

Mail::assertSent(WelcomeNewUser::class, function ($mail) use ($user) {
    return $mail->hasTo('test@example.com')
        && $mail->user->id === $user->id;
});
```

### Manuelno Testiranje

```bash
# Pošaljite test request
curl -X POST http://contractor.test/api/public/generate-invoice \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "invoice_type": "domaca",
    ...
  }'

# Proverite MailHog ili Mailtrap:
# - Email 1: "Vaša faktura je spremna" sa PDF-om
# - Email 2: "Dobrodošli na Pausalci.com" sa linkom
```

## Optimizacije za Produkciju

### 1. Queue Welcome Email
```php
// U PublicInvoiceService.php
Mail::to($user->email)
    ->queue(new \App\Mail\WelcomeNewUser($user, $resetToken));
```

### 2. Rate Limiting per Email Type
```php
// Razdvojiti rate limite za oba emaila
Cache::put("invoice_sent:{$email}", true, 60);
Cache::put("welcome_sent:{$email}", true, 3600);
```

### 3. Tracking
```php
// Logovanje za analitiku
Log::info('Invoice email sent', ['email' => $email, 'invoice_id' => $invoice->id]);
Log::info('Welcome email sent', ['email' => $email, 'user_id' => $user->id]);
```

## Monitoring

### Key Metrics

1. **Email Delivery Success Rate**
   - Invoice emails delivered
   - Welcome emails delivered
   - Bounce rate

2. **User Activation Rate**
   - Broj klikova na "Postavite šifru"
   - Broj kompletiranih registracija
   - Vreme između emaila i aktivacije

3. **Conversion Rate**
   - Free → Premium konverzije
   - Klikovi na pricing page
   - Trial sign-ups

## Troubleshooting

### Problem: Korisnik ne dobija welcome email

**Provera:**
1. Da li je korisnik stvarno nov? (`$isNewUser === true`)
2. Da li je Mail::fake() aktivan u testovima?
3. Da li je email uspešno poslat? (provera logs)
4. Da li ima grešku u password reset token generisanju?

**Rešenje:**
```bash
# Provera mail loga
tail -f storage/logs/laravel.log | grep "Welcome email"

# Provera password reset tokens
php artisan tinker
>>> DB::table('password_reset_tokens')->where('email', 'test@example.com')->get();
```

### Problem: PDF ne stiže

**Provera:**
1. Da li Gotenberg radi?
2. Da li je PDF generisan u storage?
3. Da li email ima attachment?

**Rešenje:**
```bash
# Test Gotenberg
curl http://localhost:3000/health

# Proveri storage
ls -la storage/app/public/invoices/

# Test email
php artisan tinker
>>> Mail::to('test@test.com')->send(new \App\Mail\PublicInvoiceGenerated($invoice, $pdfPath));
```

## Email Preview (Development)

### Kako Videti Emailove Lokalno

```bash
# 1. Postavi Mailtrap u .env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password

# 2. Ili koristi MailHog
# docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog
# MAIL_MAILER=smtp
# MAIL_HOST=127.0.0.1
# MAIL_PORT=1025

# 3. Pošalji test email
php artisan tinker
>>> $user = App\Models\User::first();
>>> $invoice = App\Models\Invoice::first();
>>> Mail::to('test@example.com')->send(new App\Mail\WelcomeNewUser($user, 'test-token'));
```

## Finalne Napomene

- ✅ Testovi prolaze (13/13)
- ✅ Code formatted with Pint
- ✅ Dva jasna, fokusirana emaila
- ✅ Bolja konverzija očekivana
- ✅ Ready for production

---

**Implementirano:** 2026-03-06
**Status:** ✅ Complete
**Testovi:** 13/13 passing
