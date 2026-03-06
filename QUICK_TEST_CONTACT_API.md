# Quick Test - Contact Form API

Brz vodič za testiranje Contact Form API-ja.

## ✅ 1. Provera Konfiguracije

```bash
# Proveri da li je CONTACT_EMAIL setovan
grep CONTACT_EMAIL .env

# Ako nije, dodaj:
echo "CONTACT_EMAIL=stefanrakic92@gmail.com" >> .env
```

## 🎯 2. Preview Email Template

```bash
# Pokreni server
php artisan serve

# Otvori u browseru
open http://contractor.test/email-preview/contact-form
```

Trebao bi da vidiš email sa:
- Ime pošiljaoca
- Email pošiljaoca (klikabilan)
- Naslov poruke
- Vreme slanja
- Puna poruka

## 🚀 3. Test API Request

### Uspešan Request

```bash
curl -X POST http://contractor.test/api/public/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Stefan Rakić",
    "email": "stefan@example.com",
    "subject": "Test poruka",
    "message": "Ovo je test poruka sa kontakt forme. Lorem ipsum dolor sit amet."
  }'
```

**Očekivani odgovor:**
```json
{
  "success": true,
  "message": "Hvala! Vaša poruka je uspešno poslata. Odgovorićemo vam u najkraćem roku."
}
```

### Request bez Subject-a

```bash
curl -X POST http://contractor.test/api/public/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Stefan Rakić",
    "email": "stefan@example.com",
    "message": "Poruka bez naslova - koristi se default naslov."
  }'
```

**Očekivano:** Success response (subject default: "Nova poruka sa kontakt forme")

### Validation Error - Kratka Poruka

```bash
curl -X POST http://contractor.test/api/public/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Stefan Rakić",
    "email": "stefan@example.com",
    "message": "Kratko"
  }'
```

**Očekivani odgovor:**
```json
{
  "success": false,
  "error": "Validation error",
  "details": {
    "message": ["Poruka mora imati najmanje 10 karaktera."]
  }
}
```

### Validation Error - Nema Email-a

```bash
curl -X POST http://contractor.test/api/public/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Stefan Rakić",
    "message": "Test poruka bez emaila"
  }'
```

**Očekivani odgovor:**
```json
{
  "success": false,
  "error": "Validation error",
  "details": {
    "email": ["Email adresa je obavezna."]
  }
}
```

## 📧 4. Provera Email-a

### Opcija A: Mail Log (Default)

```bash
# .env treba da ima:
MAIL_MAILER=log

# Proveri log
tail -30 storage/logs/laravel.log | grep -A 20 "Kontakt forma"
```

### Opcija B: Mailtrap (Preporučeno)

```bash
# 1. Registruj se: https://mailtrap.io/
# 2. Kopiraj credentials

# 3. Update .env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
CONTACT_EMAIL=stefanrakic92@gmail.com

# 4. Clear cache
php artisan config:clear

# 5. Pošalji test request (curl komanda gore)

# 6. Otvori Mailtrap inbox i proveri email
```

### Opcija C: Pravi Email (Gmail)

```bash
# 1. Kreiraj App Password u Google Account
# https://myaccount.google.com/apppasswords

# 2. Update .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_16_char_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@pausalci.com"
MAIL_FROM_NAME="Pausalci Contact"
CONTACT_EMAIL=stefanrakic92@gmail.com

# 3. Clear cache
php artisan config:clear

# 4. Pošalji test request

# 5. Proveri inbox na stefanrakic92@gmail.com
```

## 🧪 5. Run Automated Tests

```bash
# Svi testovi
php artisan test --filter=ContactFormApiTest

# Trebalo bi:
# ✓ 13 tests passing
# ✓ 33 assertions
```

### Pojedinačni Testovi

```bash
# Uspešan submit
php artisan test --filter=test_successful_contact_form_submission

# Validacione greške
php artisan test --filter=test_validation_error_missing_email

# Rate limiting
php artisan test --filter=test_rate_limiting_60_per_minute
```

## 🔍 6. Testiranje sa Frontend-a

### HTML Form Example

Napravi `test-contact.html`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Contact Form Test</title>
    <style>
        body { font-family: Arial; max-width: 500px; margin: 50px auto; }
        input, textarea { width: 100%; margin: 10px 0; padding: 10px; }
        button { background: #2563eb; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .success { color: green; padding: 10px; background: #d4edda; }
        .error { color: red; padding: 10px; background: #f8d7da; }
    </style>
</head>
<body>
    <h1>Contact Form Test</h1>
    <form id="contactForm">
        <input type="text" id="name" placeholder="Ime i prezime" required>
        <input type="email" id="email" placeholder="Email" required>
        <input type="text" id="subject" placeholder="Naslov (opciono)">
        <textarea id="message" rows="5" placeholder="Poruka (min 10 karaktera)" required></textarea>
        <button type="submit">Pošalji</button>
    </form>
    <div id="result"></div>

    <script>
        document.getElementById('contactForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const result = document.getElementById('result');
            result.innerHTML = 'Slanje...';

            try {
                const response = await fetch('http://contractor.test/api/public/contact', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name: document.getElementById('name').value,
                        email: document.getElementById('email').value,
                        subject: document.getElementById('subject').value,
                        message: document.getElementById('message').value
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    result.className = 'success';
                    result.innerHTML = data.message;
                    e.target.reset();
                } else {
                    result.className = 'error';
                    result.innerHTML = 'Greška: ' + (data.error || 'Nepoznata greška');
                    if (data.details) {
                        result.innerHTML += '<br><pre>' + JSON.stringify(data.details, null, 2) + '</pre>';
                    }
                }
            } catch (error) {
                result.className = 'error';
                result.innerHTML = 'Network error: ' + error.message;
            }
        });
    </script>
</body>
</html>
```

Zatim otvori fajl u browseru i testiraj.

## 📊 7. Provera Logova

```bash
# Prati logove u realnom vremenu
tail -f storage/logs/laravel.log

# Filtriraj samo contact form
tail -f storage/logs/laravel.log | grep "Contact form"

# Proveri poslednje errore
tail -100 storage/logs/laravel.log | grep ERROR
```

## 🎯 8. Rate Limiting Test

```bash
# Skripta za slanje 65 requesta
for i in {1..65}; do
  echo "Request $i"
  curl -X POST http://contractor.test/api/public/contact \
    -H "Content-Type: application/json" \
    -d '{
      "name": "Test",
      "email": "test@test.com",
      "message": "Rate limit test message number '"$i"'"
    }' -s -o /dev/null -w "Status: %{http_code}\n"
  sleep 0.1
done

# Očekivano:
# Request 1-60: Status: 200
# Request 61+: Status: 429 (Too Many Requests)
```

## ✅ Success Checklist

- [ ] `CONTACT_EMAIL` je setovan u .env
- [ ] Email preview radi
- [ ] API vraća 200 za validan request
- [ ] API vraća 422 za nevalidan request
- [ ] Email stiže na CONTACT_EMAIL
- [ ] Reply-to je setovan na pošiljaočev email
- [ ] Svi testovi prolaze (13/13)
- [ ] Rate limiting radi
- [ ] CORS omogućava requests sa frontend-a

## 🐛 Common Issues

### Problem: Email ne stiže

**Rešenje:**
```bash
# Proveri mail config
php artisan tinker
>>> config('mail');

# Proveri recipient
>>> config('mail.contact_recipient');

# Manual test
>>> Mail::to('test@test.com')->send(new \App\Mail\ContactFormMessage('Name', 'email@test.com', 'Subject', 'Message'));
```

### Problem: CORS error

**Rešenje:**
```bash
# Proveri CORS config
cat config/cors.php

# Dodaj svoj origin
# Edit config/cors.php -> 'allowed_origins'

# Clear cache
php artisan config:clear
```

### Problem: 429 Too Many Requests odmah

**Rešenje:**
```bash
# Clear cache
php artisan cache:clear

# Ili povećaj limit u routes/api.php
->middleware(['throttle:120,1'])
```

## 📚 Full Documentation

Za detaljnu dokumentaciju, vidi:
- **CONTACT_API_DOCUMENTATION.md** - Kompletna API dokumentacija
- **EMAIL_TEMPLATES_GUIDE.md** - Email template customization

---

**Ready!** 🚀 Contact Form API je spreman za produkciju.
