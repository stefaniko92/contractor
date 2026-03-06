# Contact Form API - Documentation

## Overview

Public API endpoint for receiving contact form submissions and forwarding them via email.

## Endpoint

```
POST /api/public/contact
```

## Features

- ✅ Public endpoint (no authentication required)
- ✅ Rate limited (60 requests per minute)
- ✅ CORS enabled for pausalci.com
- ✅ Email validation
- ✅ Automatic reply-to header
- ✅ Configurable recipient email via .env
- ✅ Comprehensive validation with Serbian error messages
- ✅ Full test coverage (13/13 tests passing)

## Request Format

### Headers
```http
Content-Type: application/json
```

### Body Parameters

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `name` | string | Yes | max:255 | Sender's full name |
| `email` | string | Yes | valid email, max:255 | Sender's email address |
| `subject` | string | No | max:255 | Message subject (defaults to "Nova poruka sa kontakt forme") |
| `message` | string | Yes | min:10, max:5000 | Message content |

### Example Request

```json
{
  "name": "Stefan Rakić",
  "email": "stefan@example.com",
  "subject": "Pitanje o pricing planovima",
  "message": "Pozdrav,\n\nZainteresovan sam za više informacija o vašim pricing planovima...\n\nSrdačan pozdrav,\nStefan"
}
```

## Response Format

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Hvala! Vaša poruka je uspešno poslata. Odgovorićemo vam u najkraćem roku."
}
```

### Validation Error (422 Unprocessable Entity)

```json
{
  "success": false,
  "error": "Validation error",
  "details": {
    "name": ["Ime je obavezno."],
    "email": ["Email adresa nije validna."],
    "message": ["Poruka mora imati najmanje 10 karaktera."]
  }
}
```

### Rate Limit Error (429 Too Many Requests)

```json
{
  "message": "Too Many Requests",
  "exception": "Symfony\\Component\\HttpKernel\\Exception\\TooManyRequestsHttpException"
}
```

### Server Error (500 Internal Server Error)

```json
{
  "success": false,
  "error": "Greška prilikom slanja poruke.",
  "message": "Molimo pokušajte ponovo."
}
```

## Configuration

### Environment Variables

Add to your `.env` file:

```env
# Contact Form Recipient
CONTACT_EMAIL=stefanrakic92@gmail.com

# Mail Configuration (if not already set)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@pausalci.com"
MAIL_FROM_NAME="Pausalci Contact Form"
```

### Changing Recipient Email

You can change the recipient email in two ways:

**1. Via .env file (Recommended):**
```env
CONTACT_EMAIL=newrecipient@example.com
```

**2. Via config/mail.php:**
```php
'contact_recipient' => env('CONTACT_EMAIL', 'default@example.com'),
```

Then clear config cache:
```bash
php artisan config:clear
```

## Email Template

The email sent to the recipient includes:

- **Subject:** "Kontakt forma: [User's Subject]"
- **Reply-To:** User's email address (for easy replies)
- **Body:**
  - Sender name
  - Sender email (clickable)
  - Subject
  - Timestamp
  - Full message content
  - Quick reply tip

### Email Preview

To preview the email template in development:

```bash
# Start server
php artisan serve

# Open in browser
http://contractor.test/email-preview/contact-form
```

## Rate Limiting

- **Limit:** 60 requests per minute per IP address
- **Purpose:** Prevent spam and abuse
- **Response:** 429 Too Many Requests after limit exceeded

To adjust rate limit, edit `routes/api.php`:

```php
Route::post('/public/contact', [ContactController::class, 'send'])
    ->middleware(['throttle:120,1']) // 120 requests per minute
    ->name('api.public.contact');
```

## Usage Examples

### cURL

```bash
curl -X POST http://contractor.test/api/public/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Stefan Rakić",
    "email": "stefan@example.com",
    "subject": "Test poruka",
    "message": "Ovo je test poruka sa kontakt forme."
  }'
```

### JavaScript (Fetch)

```javascript
async function sendContactMessage(formData) {
  try {
    const response = await fetch('https://pausalci.com/api/public/contact', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        name: formData.name,
        email: formData.email,
        subject: formData.subject,
        message: formData.message
      })
    });

    const result = await response.json();

    if (response.ok) {
      alert(result.message);
      // Clear form
    } else if (response.status === 422) {
      // Show validation errors
      console.error('Validation errors:', result.details);
    } else {
      alert('Greška: ' + result.error);
    }
  } catch (error) {
    console.error('Network error:', error);
    alert('Greška pri slanju poruke. Proverite internet konekciju.');
  }
}
```

### React Example

```jsx
import { useState } from 'react';

function ContactForm() {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    subject: '',
    message: ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    setSuccess(false);

    try {
      const response = await fetch('https://pausalci.com/api/public/contact', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
      });

      const result = await response.json();

      if (response.ok) {
        setSuccess(true);
        setFormData({ name: '', email: '', subject: '', message: '' });
      } else {
        setError(result.error || 'Greška pri slanju');
      }
    } catch (err) {
      setError('Greška pri slanju poruke');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        placeholder="Ime i prezime"
        value={formData.name}
        onChange={(e) => setFormData({...formData, name: e.target.value})}
        required
      />
      <input
        type="email"
        placeholder="Email"
        value={formData.email}
        onChange={(e) => setFormData({...formData, email: e.target.value})}
        required
      />
      <input
        type="text"
        placeholder="Naslov (opciono)"
        value={formData.subject}
        onChange={(e) => setFormData({...formData, subject: e.target.value})}
      />
      <textarea
        placeholder="Poruka (min 10 karaktera)"
        value={formData.message}
        onChange={(e) => setFormData({...formData, message: e.target.value})}
        required
        minLength={10}
      />
      <button type="submit" disabled={loading}>
        {loading ? 'Slanje...' : 'Pošalji poruku'}
      </button>

      {success && <div className="success">Poruka uspešno poslata!</div>}
      {error && <div className="error">{error}</div>}
    </form>
  );
}
```

### Vue 3 Example

```vue
<template>
  <form @submit.prevent="handleSubmit">
    <input v-model="form.name" type="text" placeholder="Ime i prezime" required />
    <input v-model="form.email" type="email" placeholder="Email" required />
    <input v-model="form.subject" type="text" placeholder="Naslov (opciono)" />
    <textarea v-model="form.message" placeholder="Poruka" required minlength="10" />
    <button type="submit" :disabled="loading">
      {{ loading ? 'Slanje...' : 'Pošalji poruku' }}
    </button>

    <div v-if="success" class="success">Poruka uspešno poslata!</div>
    <div v-if="error" class="error">{{ error }}</div>
  </form>
</template>

<script setup>
import { ref } from 'vue';

const form = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
});

const loading = ref(false);
const error = ref(null);
const success = ref(false);

const handleSubmit = async () => {
  loading.value = true;
  error.value = null;
  success.value = false;

  try {
    const response = await fetch('https://pausalci.com/api/public/contact', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value)
    });

    const result = await response.json();

    if (response.ok) {
      success.value = true;
      form.value = { name: '', email: '', subject: '', message: '' };
    } else {
      error.value = result.error;
    }
  } catch (err) {
    error.value = 'Greška pri slanju poruke';
  } finally {
    loading.value = false;
  }
};
</script>
```

## Testing

### Run Tests

```bash
# All contact form tests
php artisan test --filter=ContactFormApiTest

# Specific test
php artisan test --filter=test_successful_contact_form_submission

# Expected: 13/13 tests passing
```

### Test Cases Covered

1. ✅ Successful submission with all fields
2. ✅ Successful submission without subject (uses default)
3. ✅ Validation: missing name
4. ✅ Validation: missing email
5. ✅ Validation: invalid email format
6. ✅ Validation: missing message
7. ✅ Validation: message too short (<10 chars)
8. ✅ Validation: message too long (>5000 chars)
9. ✅ Rate limiting is configured
10. ✅ Reply-to header is set correctly
11. ✅ Long name is accepted (255 chars)
12. ✅ Name over max length fails (>255 chars)
13. ✅ Email subject format is correct

### Manual Testing

```bash
# 1. Setup test email (Mailtrap recommended)
# Update .env with Mailtrap credentials

# 2. Send test request
curl -X POST http://contractor.test/api/public/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "subject": "Test Message",
    "message": "This is a test message to verify the contact form API."
  }'

# 3. Check email in Mailtrap inbox
# 4. Verify reply-to is set to test@example.com
# 5. Try replying directly from email
```

## Security

### Protection Against Spam

1. **Rate Limiting:** 60 requests/minute per IP
2. **Email Validation:** RFC-compliant email validation
3. **Length Limits:**
   - Name: max 255 chars
   - Subject: max 255 chars
   - Message: min 10, max 5000 chars
4. **CORS:** Only allowed origins can submit

### Protection Against XSS

- All user input is automatically escaped in Blade templates
- Email content is rendered as plain text (pre-wrap)

### Protection Against Email Injection

- Laravel's Mail facade automatically sanitizes headers
- Subject line is concatenated safely
- No raw header manipulation

## Monitoring

### Logging

All contact form submissions are logged:

```bash
# View logs
tail -f storage/logs/laravel.log | grep "Contact form"
```

Log entries include:
- Sender email
- Error messages (if any)
- Stack trace (on failures)

### Metrics to Track

1. **Submission Rate**
   - Total submissions per day/week/month
   - Peak submission times

2. **Success Rate**
   - Successful deliveries
   - Failed submissions
   - Validation errors

3. **Response Time**
   - API response time
   - Email delivery time

4. **Spam Detection**
   - Rate limit hits
   - Invalid email patterns
   - Suspicious messages

## Troubleshooting

### Email not received

**Check:**
```bash
# 1. Laravel logs
tail -f storage/logs/laravel.log | grep "Contact form"

# 2. Mail configuration
php artisan tinker
>>> config('mail.contact_recipient');

# 3. Test email sending
>>> Mail::to('test@test.com')->send(new \App\Mail\ContactFormMessage('Name', 'email@test.com', 'Subject', 'Message'));
```

**Common issues:**
- SMTP credentials incorrect
- Recipient email in spam folder
- Mail driver set to 'log' instead of 'smtp'
- Firewall blocking SMTP port

### Rate limit triggered too easily

**Solution:**
Increase rate limit in `routes/api.php`:

```php
->middleware(['throttle:120,1']) // 120 per minute instead of 60
```

### CORS errors from frontend

**Check:**
1. Origin is whitelisted in `config/cors.php`
2. Correct headers are sent
3. Using HTTPS in production

## Files Created

1. **Controller:** `app/Http/Controllers/Api/ContactController.php`
2. **Request:** `app/Http/Requests/SendContactMessageRequest.php`
3. **Mailable:** `app/Mail/ContactFormMessage.php`
4. **View:** `resources/views/emails/contact-form-message.blade.php`
5. **Tests:** `tests/Feature/ContactFormApiTest.php`
6. **Config:** Updated `config/mail.php` and `config/cors.php`
7. **Routes:** Updated `routes/api.php`
8. **Preview:** Updated `routes/web-email-preview.php`

## Production Checklist

Before deploying to production:

- [ ] Set `CONTACT_EMAIL` in production `.env`
- [ ] Configure SMTP credentials
- [ ] Test email delivery
- [ ] Verify CORS settings
- [ ] Check rate limiting
- [ ] Monitor logs for spam
- [ ] Setup email notifications for failures
- [ ] Configure queue for async email sending (optional)

## Future Enhancements

Potential improvements:

- reCAPTCHA integration
- Honeypot field for spam detection
- Auto-responder to sender
- File attachment support
- Multiple recipient support
- Email templates per subject category
- Admin dashboard for submissions
- Database storage of submissions

---

**Status:** ✅ Production Ready
**Version:** 1.0
**Last Updated:** 2026-03-06
**Tests:** 13/13 passing
