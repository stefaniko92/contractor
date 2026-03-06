# Public API Endpoints - Summary

Brz pregled svih javnih API endpointa.

## 📋 Overview

Pausalci.com ima **2 javna API endpointa**:

1. **Invoice Generator** - Kreiranje faktura i automatska registracija
2. **Contact Form** - Slanje poruka sa kontakt forme

Oba endpointa su:
- ✅ Javni (bez autentifikacije)
- ✅ CORS enabled
- ✅ Rate limited
- ✅ Fully tested
- ✅ Production ready

---

## 🧾 1. Invoice Generator API

### Endpoint
```
POST /api/public/generate-invoice
```

### Purpose
Omogućava kreiranje faktura bez potrebe za registracijom. Automatski kreira nalog i šalje PDF na email.

### Features
- Automatska registracija korisnika
- PDF generisanje (Gotenberg)
- Dual email flow (faktura + dobrodošlica)
- Rate limiting: 3 fakture po email-u u 30 dana
- 13/13 testova prolazi

### Quick Example
```bash
curl -X POST http://contractor.test/api/public/generate-invoice \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "invoice_type": "domaca",
    "seller": {
      "pib": "123456789",
      "company_name": "Moja Firma d.o.o.",
      "address": "Beograd"
    },
    "buyer": {
      "name": "Kupac",
      "address": "Novi Sad"
    },
    "invoice": {
      "date_issued": "2025-01-15",
      "date_due": "2025-02-15",
      "place": "Beograd",
      "currency": "RSD"
    },
    "items": [{
      "title": "Usluga",
      "type": "usluga",
      "unit": "sat",
      "quantity": 10,
      "unit_price": 5000
    }]
  }'
```

### Response

**Za novog korisnika:**
```json
{
  "success": true,
  "message": "Faktura je poslata na user@example.com.",
  "user_created": true,
  "reset_url": "https://app.pausalci.com/admin/password-reset/reset?token=...&email=user%40example.com"
}
```

**Za postojećeg korisnika:**
```json
{
  "success": true,
  "message": "Faktura je poslata na user@example.com.",
  "user_created": false
}
```

### Documentation
- **PUBLIC_INVOICE_API_SUMMARY.md** - Kompletna dokumentacija
- **QUICK_START_PUBLIC_API.md** - Brzi start
- **DUAL_EMAIL_FLOW.md** - Email flow detalji

---

## 📧 2. Contact Form API

### Endpoint
```
POST /api/public/contact
```

### Purpose
Prima poruke sa kontakt forme i šalje ih na konfigurisanu email adresu.

### Features
- Konfigurisabilan recipient email (`.env`)
- Automatski reply-to header
- Rate limiting: 60 requesta/min
- Validacija sa srpskim porukama
- 13/13 testova prolazi

### Quick Example
```bash
curl -X POST http://contractor.test/api/public/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Stefan Rakić",
    "email": "stefan@example.com",
    "subject": "Pitanje o pricing-u",
    "message": "Zanima me više informacija o vašim pricing planovima..."
  }'
```

### Response
```json
{
  "success": true,
  "message": "Hvala! Vaša poruka je uspešno poslata. Odgovorićemo vam u najkraćem roku."
}
```

### Configuration
```env
# .env
CONTACT_EMAIL=stefanrakic92@gmail.com
```

### Documentation
- **CONTACT_API_DOCUMENTATION.md** - Kompletna dokumentacija
- **QUICK_TEST_CONTACT_API.md** - Brzi test guide

---

## 🔒 Rate Limiting

| Endpoint | Limit | Type |
|----------|-------|------|
| `/api/public/generate-invoice` | 3 per email per 30 days | Email-based |
| `/api/public/generate-invoice` | 10 per minute | IP-based |
| `/api/public/contact` | 60 per minute | IP-based |

---

## 🌐 CORS Configuration

```php
// config/cors.php
'allowed_origins' => [
    'https://pausalci.com',
    'https://www.pausalci.com',
    'http://localhost:3000', // dev only
    'http://localhost:5173', // dev only
]
```

---

## 📧 Email Previews (Development)

```bash
# Invoice email
http://contractor.test/email-preview/public-invoice

# Welcome email (za nove korisnike)
http://contractor.test/email-preview/welcome-user

# Contact form email
http://contractor.test/email-preview/contact-form
```

---

## 🧪 Testing

### Run All Public API Tests

```bash
# Invoice API tests
php artisan test --filter=PublicInvoiceApiTest
# ✓ 13/13 passing

# Contact API tests
php artisan test --filter=ContactFormApiTest
# ✓ 13/13 passing

# Total: 26/26 tests passing
```

### Quick Manual Test

```bash
# Invoice API
curl -X POST http://contractor.test/api/public/generate-invoice \
  -H "Content-Type: application/json" \
  -d @tests/fixtures/valid-invoice.json

# Contact API
curl -X POST http://contractor.test/api/public/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test",
    "email": "test@test.com",
    "message": "Test message"
  }'
```

---

## 🚀 Production Deployment

### Environment Variables Required

```env
# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_api_key
MAIL_FROM_ADDRESS="noreply@pausalci.com"
MAIL_FROM_NAME="Pausalci"

# Contact Form Recipient
CONTACT_EMAIL=stefanrakic92@gmail.com

# Gotenberg (for PDFs)
GOTENBERG_URL=http://gotenberg:3000

# App
APP_URL=https://pausalci.com
```

### Deployment Checklist

- [ ] Update `.env` with production values
- [ ] Verify Gotenberg is running
- [ ] Test email delivery (SMTP working)
- [ ] Verify CORS settings
- [ ] Check rate limiting
- [ ] Run all tests (26/26 passing)
- [ ] Monitor logs for errors
- [ ] Setup queue workers (optional, for async emails)

---

## 📊 Monitoring

### Key Metrics

**Invoice API:**
- Invoices generated per day
- New user registrations
- Rate limit hits
- PDF generation failures
- Email delivery rate

**Contact API:**
- Messages received per day
- Spam rate (if high rate limit hits)
- Email delivery rate
- Response time

### Logs

```bash
# Invoice API
tail -f storage/logs/laravel.log | grep "PublicInvoice"

# Contact API
tail -f storage/logs/laravel.log | grep "Contact form"

# All API errors
tail -f storage/logs/laravel.log | grep "ERROR"
```

---

## 🔧 Troubleshooting

### Common Issues

| Problem | Solution |
|---------|----------|
| Email not received | Check SMTP credentials, check spam folder |
| CORS error | Verify origin in `config/cors.php` |
| PDF not generated | Ensure Gotenberg is running |
| Rate limit too strict | Adjust in `routes/api.php` |
| 500 errors | Check `storage/logs/laravel.log` |

### Debug Mode

```env
# Enable for detailed errors (dev only!)
APP_DEBUG=true
```

---

## 📚 Documentation Index

### Invoice Generator
1. **PUBLIC_INVOICE_API_SUMMARY.md** - Complete overview
2. **QUICK_START_PUBLIC_API.md** - Quick setup guide
3. **DUAL_EMAIL_FLOW.md** - Email flow details
4. **EMAIL_TEMPLATES_GUIDE.md** - Email customization

### Contact Form
5. **CONTACT_API_DOCUMENTATION.md** - Complete API docs
6. **QUICK_TEST_CONTACT_API.md** - Testing guide

### General
7. **API_ENDPOINTS_SUMMARY.md** - This file
8. **README.md** - Project overview

---

## 🎯 Quick Links

- **Invoice API Endpoint:** `POST /api/public/generate-invoice`
- **Contact API Endpoint:** `POST /api/public/contact`
- **Email Previews:** `http://contractor.test/email-preview/*`
- **API Tests:** `tests/Feature/PublicInvoiceApiTest.php` + `ContactFormApiTest.php`

---

**Status:** ✅ Production Ready
**Total Endpoints:** 2
**Total Tests:** 26/26 passing
**Last Updated:** 2026-03-06
