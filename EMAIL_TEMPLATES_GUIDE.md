# Email Templates - Developer Guide

## 📧 Pregled Email Flow-a

Kada korisnik kreira fakturu preko javnog API-ja, sistem automatski šalje **jedan ili dva emaila**:

### Za Nove Korisnike (2 emaila)

```
1. "Vaša faktura je spremna"
   ↓ PDF prilog ✓

2. "Dobrodošli na Pausalci.com"
   ↓ Link za postavljanje šifre ✓
```

### Za Postojeće Korisnike (1 email)

```
1. "Vaša faktura je spremna"
   ↓ PDF prilog ✓
```

---

## 📝 Template 1: Invoice Email

### Fajl
`resources/views/emails/public-invoice-generated.blade.php`

### Kada se šalje
- **Uvek** - za sve korisnike
- Odmah nakon kreiranja fakture

### Sadržaj
```
┌─────────────────────────────────────┐
│  Vaša faktura je spremna! 🎉       │
├─────────────────────────────────────┤
│                                     │
│  Pozdrav,                           │
│                                     │
│  Uspešno ste kreirali fakturu      │
│  1/2025 putem našeg besplatnog      │
│  generatora.                        │
│                                     │
│  Faktura je u prilogu.              │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Detalji fakture:            │   │
│  │ Broj: 1/2025                │   │
│  │ Datum: 15.01.2025           │   │
│  │ Iznos: 50.000,00 RSD        │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ 💡 Premium Plan              │   │
│  │ • Neograničene fakture      │   │
│  │ • eFaktura integracija      │   │
│  │ • Praćenje plaćanja         │   │
│  │                             │   │
│  │ [Pogledajte planove]        │   │
│  └─────────────────────────────┘   │
│                                     │
└─────────────────────────────────────┘

📎 Attachment: Faktura-1-2025.pdf
```

### Preview
```bash
# Pokreni server
php artisan serve

# Otvori u browseru
open http://contractor.test/email-preview/public-invoice
```

---

## 📝 Template 2: Welcome Email

### Fajl
`resources/views/emails/welcome-new-user.blade.php`

### Kada se šalje
- **Samo za nove korisnike**
- Odmah nakon invoice emaila

### Sadržaj
```
┌─────────────────────────────────────┐
│  🎉 Dobrodošli na Pausalci.com!    │
│  Automatski smo kreirali besplatan │
│  nalog za vas                       │
├─────────────────────────────────────┤
│                                     │
│  Pozdrav Stefan Rakić,              │
│                                     │
│  PDF faktura stiže na email za par  │
│  sekundi.                           │
│                                     │
│  Da biste upravljali fakturama,     │
│  potrebno je samo da postavite      │
│  šifru:                             │
│                                     │
│     ┌───────────────────────┐      │
│     │  Postavite šifru  🔑  │      │
│     └───────────────────────┘      │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ ✨ Besplatni nalog:         │   │
│  │ • 3 fakture mesečno         │   │
│  │ • PDF generisanje           │   │
│  │ • Čuvanje klijenata         │   │
│  │ • Osnovni izveštaji         │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ 🚀 Premium - Bez Limita     │   │
│  │ ✓ Neograničene fakture      │   │
│  │ ✓ Automatska eFaktura       │   │
│  │ ✓ Praćenje plaćanja         │   │
│  │ ✓ Napredni izveštaji        │   │
│  │                             │   │
│  │ [Pogledajte cene]           │   │
│  └─────────────────────────────┘   │
│                                     │
│  💡 Prijavite se odmah i           │
│     istražite sve funkcije!        │
│                                     │
└─────────────────────────────────────┘
```

### Preview
```bash
# Pokreni server
php artisan serve

# Otvori u browseru
open http://contractor.test/email-preview/welcome-user
```

---

## 🎨 Stilske Smernice

### Boje

```css
/* Primarna - Plava */
#2563eb  /* CTA dugmad, naslovi */

/* Sekundarna - Tamnoplava */
#0369a1  /* Premium sekcija */

/* Akcenat - Žuta */
#f59e0b  /* Premium highlight */

/* Pozadine */
#f8f9fa  /* Glavni wrapper */
#ffffff  /* Beli blokovi */
#e0f2fe  /* Plavi info box */
#fef3c7  /* Žuti premium box */

/* Tekst */
#333333  /* Glavni tekst */
#6b7280  /* Sekundarni tekst */
#9ca3af  /* Footer tekst */
```

### Typography

```css
/* Naslovi */
h1: 28px / #2563eb / bold
h2: 18px / #1f2937 / bold

/* Body */
p: 16px / #333333 / normal
small: 14px / #6b7280 / normal
```

### Spacing

```css
/* Padding */
Wrapper: 40px 30px
Sections: 25px 20px
Buttons: 15px 40px

/* Border Radius */
Wrapper: 10px
Sections: 8px
Buttons: 8px
```

---

## 🔧 Izmena Emailova

### 1. Izmena Invoice Email Template

```bash
# Otvori fajl
code resources/views/emails/public-invoice-generated.blade.php

# Izmeni sadržaj
# Dostupne varijable:
# - $invoice (Invoice model sa relationships)
#   - $invoice->invoice_number
#   - $invoice->issue_date
#   - $invoice->due_date
#   - $invoice->amount
#   - $invoice->currency

# Preview promene
open http://contractor.test/email-preview/public-invoice

# Test u produkciji
php artisan tinker
>>> $invoice = Invoice::first();
>>> Mail::to('test@test.com')->send(new PublicInvoiceGenerated($invoice, '/path/to/pdf'));
```

### 2. Izmena Welcome Email Template

```bash
# Otvori fajl
code resources/views/emails/welcome-new-user.blade.php

# Izmeni sadržaj
# Dostupne varijable:
# - $user (User model)
#   - $user->name
#   - $user->email
# - $resetUrl (string - link za reset šifre)

# Preview promene
open http://contractor.test/email-preview/welcome-user

# Test u produkciji
php artisan tinker
>>> $user = User::first();
>>> Mail::to('test@test.com')->send(new WelcomeNewUser($user, 'test-token'));
```

---

## 📊 Email Metrics

### Tracking Points

```php
// U PublicInvoiceService.php možete dodati:

// 1. Invoice email tracking
Log::info('Invoice email sent', [
    'email' => $user->email,
    'invoice_id' => $invoice->id,
    'invoice_number' => $invoice->invoice_number,
]);

// 2. Welcome email tracking
Log::info('Welcome email sent', [
    'email' => $user->email,
    'user_id' => $user->id,
    'created_at' => $user->created_at,
]);
```

### Metrics za Praćenje

1. **Email Delivery Rate**
   - Uspešno poslati emailovi
   - Bounced emailovi
   - Failed deliveries

2. **Open Rate**
   - Invoice email open rate
   - Welcome email open rate
   - Prosečno vreme do otvaranja

3. **Click-Through Rate**
   - Klikovi na "Postavite šifru"
   - Klikovi na "Pogledajte planove"
   - Klikovi na pricing page

4. **Conversion Rate**
   - Completed registrations (set password)
   - Free → Premium upgrades
   - Time to conversion

---

## 🧪 Testiranje

### Unit Test

```php
// tests/Feature/EmailTest.php
public function test_invoice_email_sent_to_user()
{
    Mail::fake();

    $invoice = Invoice::factory()->create();

    Mail::to('test@test.com')->send(
        new PublicInvoiceGenerated($invoice, '/path/to/pdf')
    );

    Mail::assertSent(PublicInvoiceGenerated::class, function ($mail) {
        return $mail->hasTo('test@test.com');
    });
}

public function test_welcome_email_sent_to_new_user()
{
    Mail::fake();

    $user = User::factory()->create();

    Mail::to($user->email)->send(
        new WelcomeNewUser($user, 'token')
    );

    Mail::assertSent(WelcomeNewUser::class);
}
```

### Manual Testing

```bash
# 1. Setup Mailtrap
# https://mailtrap.io/
# Copy credentials to .env

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS="noreply@pausalci.com"
MAIL_FROM_NAME="Pausalci"

# 2. Send test emails
php artisan tinker

# Invoice email
>>> $invoice = Invoice::first();
>>> Mail::to('test@test.com')->send(new PublicInvoiceGenerated($invoice, storage_path('app/test.pdf')));

# Welcome email
>>> $user = User::first();
>>> Mail::to('test@test.com')->send(new WelcomeNewUser($user, 'test-token'));

# 3. Check Mailtrap inbox
# Open https://mailtrap.io/inboxes
```

---

## 🚀 Production Setup

### 1. Email Service Provider

Preporučeni provideri:
- **SendGrid** (najbolji deliverability)
- **Mailgun** (dobar API)
- **Amazon SES** (najjeftiniji)
- **Postmark** (najbolji za transactional emails)

### 2. .env Configuration

```env
# Production Email Settings
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@pausalci.com"
MAIL_FROM_NAME="Pausalci"
```

### 3. Queue Configuration

Za bolju performansu, stavite emailove u queue:

```php
// U PublicInvoiceService.php

// Invoice email (odmah)
Mail::to($user->email)->send(
    new PublicInvoiceGenerated($invoice, $pdfPath)
);

// Welcome email (queue)
Mail::to($user->email)->queue(
    new WelcomeNewUser($user, $resetToken)
);
```

```bash
# Pokreni queue worker
php artisan queue:work --queue=emails
```

---

## 🐛 Troubleshooting

### Email ne stiže

**Proveri:**
```bash
# 1. Mail logs
tail -f storage/logs/laravel.log | grep "Mail"

# 2. Queue jobs (ako koristite queue)
php artisan queue:failed

# 3. Email configuration
php artisan tinker
>>> config('mail');
```

**Česti problemi:**
- SMTP credentials pogrešni
- From address ne postoji na domenu
- Port blokiran na serveru
- Queue worker nije pokrenut

### Link u emailu ne radi

**Proveri:**
```bash
# APP_URL mora biti tačan
APP_URL=https://pausalci.com

# Password reset link generation
php artisan tinker
>>> Password::createToken(User::first());
>>> route('password.reset', ['token' => 'test', 'email' => 'test@test.com']);
```

### PDF nije attachment

**Proveri:**
```bash
# Da li PDF postoji?
ls -la storage/app/public/invoices/

# Da li je path tačan?
php artisan tinker
>>> file_exists('/path/to/pdf');

# Da li je Mailable property $pdfPath setovan?
>>> $mail = new PublicInvoiceGenerated($invoice, '/path/to/pdf');
>>> $mail->pdfPath;
```

---

## 📚 Resources

### Laravel Mail Documentation
- https://laravel.com/docs/mail
- https://laravel.com/docs/notifications

### Email Design Best Practices
- https://www.campaignmonitor.com/resources/guides/email-design/
- https://www.goodemailcode.com/

### Testing Tools
- Mailtrap: https://mailtrap.io/
- MailHog: https://github.com/mailhog/MailHog
- Email on Acid: https://www.emailonacid.com/

---

**Last Updated:** 2026-03-06
**Status:** ✅ Production Ready
