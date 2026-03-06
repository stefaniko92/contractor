# Quick Start - Public Invoice API

Brz vodič za testiranje javnog API-ja za kreiranje faktura.

## ✅ Preduslovi

```bash
# 1. Proveri da li je Gotenberg pokrenut (za PDF)
curl http://localhost:3000/health
# Očekivano: {"status":"up"}

# 2. Proveri Laravel
php artisan --version
# Očekivano: Laravel Framework 12.x

# 3. Pokreni aplikaciju
php artisan serve
# Dostupno na: http://contractor.test
```

## 🚀 Brzo Testiranje

### 1. Preview Emailova (bez slanja)

```bash
# Invoice email
open http://contractor.test/email-preview/public-invoice

# Welcome email
open http://contractor.test/email-preview/welcome-user
```

### 2. Test API Endpoint

```bash
# Postavi mail na log mode (da ne šalje pravi email)
echo "MAIL_MAILER=log" >> .env

# Test request
curl -X POST http://contractor.test/api/public/generate-invoice \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "invoice_type": "domaca",
    "seller": {
      "pib": "123456789",
      "mb": "12345678",
      "company_name": "Test Company d.o.o.",
      "address": "Terazije 1",
      "city": "Beograd",
      "phone": "0112345678"
    },
    "buyer": {
      "name": "Kupac d.o.o.",
      "pib": "987654321",
      "address": "Ulica kupca 456",
      "city": "Novi Sad"
    },
    "invoice": {
      "date_issued": "2025-01-15",
      "date_due": "2025-02-15",
      "place": "Beograd",
      "currency": "RSD"
    },
    "items": [
      {
        "title": "Konsultantske usluge",
        "type": "usluga",
        "unit": "sat",
        "quantity": 10,
        "unit_price": 5000
      }
    ]
  }'

# Očekivani response:
# {
#   "success": true,
#   "message": "Faktura je poslata na test@example.com.",
#   "user_created": true
# }
```

### 3. Proveri Rezultate

```bash
# Proveri korisnika
php artisan tinker
>>> User::where('email', 'test@example.com')->first();

# Proveri fakturu
>>> Invoice::latest()->first();

# Proveri PDF
>>> ls -la storage/app/public/invoices/

# Proveri email log
>>> tail -n 50 storage/logs/laravel.log | grep "Mail"
```

## 📧 Testiranje Emailova

### Setup Mailtrap (Preporučeno)

1. **Registruj se**: https://mailtrap.io/
2. **Kopiraj credentials** iz inbox settings
3. **Update .env**:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username_here
MAIL_PASSWORD=your_password_here
MAIL_FROM_ADDRESS="noreply@pausalci.com"
MAIL_FROM_NAME="Pausalci"
```

4. **Test slanje**:

```bash
# Clear config cache
php artisan config:clear

# Send test email
php artisan tinker
>>> $user = User::first();
>>> $invoice = Invoice::first();
>>> Mail::to('test@test.com')->send(new \App\Mail\PublicInvoiceGenerated($invoice, storage_path('app/test.pdf')));
>>> Mail::to('test@test.com')->send(new \App\Mail\WelcomeNewUser($user, 'test-token-123'));
```

5. **Proveri Mailtrap inbox** - trebalo bi da vidiš 2 emaila

### Alternativa: MailHog

```bash
# Pokreni MailHog (Docker)
docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog

# Update .env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025

# Otvori MailHog UI
open http://localhost:8025

# Pošalji test email
php artisan tinker
>>> Mail::to('test@test.com')->send(new \App\Mail\WelcomeNewUser(User::first(), 'token'));
```

## 🧪 Run Tests

```bash
# Svi testovi
php artisan test --filter=PublicInvoiceApiTest

# Pojedinačni test
php artisan test --filter=test_successful_invoice_generation_with_new_user

# Očekivano: 13/13 passing
```

## 📝 Rate Limiting Test

```bash
# Prvi request - OK
curl -X POST http://contractor.test/api/public/generate-invoice -H "Content-Type: application/json" -d '...'
# Response: 200 OK

# Drugi request (isti email) - OK
curl -X POST http://contractor.test/api/public/generate-invoice -H "Content-Type: application/json" -d '...'
# Response: 200 OK

# Treći request (isti email) - OK
curl -X POST http://contractor.test/api/public/generate-invoice -H "Content-Type: application/json" -d '...'
# Response: 200 OK

# Četvrti request (isti email) - BLOCKED
curl -X POST http://contractor.test/api/public/generate-invoice -H "Content-Type: application/json" -d '...'
# Response: 429 Too Many Requests
# {
#   "success": false,
#   "error": "Dostigli ste maksimalan broj besplatnih faktura (3) u zadnjih 30 dana.",
#   "message": "Registrujte se za neograničeno kreiranje faktura."
# }

# Proveri cache
php artisan tinker
>>> Cache::get('public_invoice_count:' . md5('test@example.com'));
# Output: 3
```

## 🎯 Integration sa Frontend-om

### JavaScript Fetch Example

```javascript
async function generateInvoice(data) {
  try {
    const response = await fetch('http://contractor.test/api/public/generate-invoice', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data)
    });

    const result = await response.json();

    if (response.ok) {
      // Success
      alert(`Faktura poslata na ${data.email}`);
      if (result.user_created) {
        alert('Proverite email za aktivaciju naloga!');
      }
    } else if (response.status === 429) {
      // Rate limited
      alert(result.error);
    } else if (response.status === 422) {
      // Validation error
      console.error('Validation errors:', result.details);
    } else {
      // Other error
      alert('Greška: ' + result.error);
    }
  } catch (error) {
    console.error('Network error:', error);
    alert('Greška pri slanju. Proverite internet konekciju.');
  }
}

// Poziv funkcije
generateInvoice({
  email: 'test@example.com',
  invoice_type: 'domaca',
  seller: { /* ... */ },
  buyer: { /* ... */ },
  invoice: { /* ... */ },
  items: [{ /* ... */ }]
});
```

### React Example

```jsx
import { useState } from 'react';

function InvoiceGenerator() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleSubmit = async (formData) => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('http://contractor.test/api/public/generate-invoice', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      });

      const result = await response.json();

      if (response.ok) {
        alert('✓ Faktura je poslata na vaš email!');
        if (result.user_created) {
          alert('✉️ Proverite email za aktivaciju naloga');
        }
      } else {
        setError(result.error);
      }
    } catch (err) {
      setError('Greška pri slanju');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={(e) => {
      e.preventDefault();
      handleSubmit(/* form data */);
    }}>
      {/* Form fields */}
      <button disabled={loading}>
        {loading ? 'Slanje...' : 'Generiši fakturu'}
      </button>
      {error && <div className="error">{error}</div>}
    </form>
  );
}
```

## 🔍 Debugging

### Enable Debug Mode

```bash
# .env
APP_DEBUG=true

# Test sa detaljnim errorima
curl -X POST http://contractor.test/api/public/generate-invoice ...
```

### Check Logs

```bash
# Laravel log
tail -f storage/logs/laravel.log

# Filtriraj samo API errore
tail -f storage/logs/laravel.log | grep "PublicInvoice"

# Proveri mail log
tail -f storage/logs/laravel.log | grep "Mail"
```

### Common Issues

| Problem | Rešenje |
|---------|---------|
| `cURL error 6` | Gotenberg nije pokrenut - `docker start gotenberg` |
| `SMTP error` | Proveri `.env` mail credentials |
| `429 Too Many Requests` | Clear cache - `php artisan cache:clear` |
| `REGEXP error` | SQLite - vidi `tests/TestCase.php` |
| `PDF not found` | Proveri `storage/app/public/invoices/` |

## 📚 Additional Resources

- **Full Documentation**: `PUBLIC_INVOICE_API_SUMMARY.md`
- **Email Guide**: `EMAIL_TEMPLATES_GUIDE.md`
- **Dual Email Flow**: `DUAL_EMAIL_FLOW.md`
- **API Tests**: `tests/Feature/PublicInvoiceApiTest.php`

## 🎉 Success Checklist

- [ ] Gotenberg is running
- [ ] API endpoint responds (200)
- [ ] Invoice created in database
- [ ] PDF generated in storage
- [ ] Invoice email sent
- [ ] Welcome email sent (for new users)
- [ ] Rate limiting works (4th request blocked)
- [ ] Tests passing (13/13)
- [ ] Email previews work
- [ ] Frontend integration works

---

**Ready to deploy?** Check `PUBLIC_INVOICE_API_SUMMARY.md` for production setup.
