# Pausalci Chatbot - Quick Reference Guide

## Quick Navigation Paths

### Creating Things:
- **New Invoice**: Fakturisanje → Fakture → Nova Faktura
- **New Client**: Fakturisanje → Klijenti → Novi Klijent
- **New Bank Account**: Moja kompanija → Bankovni računi → Novi

### Viewing Information:
- **Dashboard**: Home page (after login)
- **Income Summary**: Moja kompanija → Prihodi
- **Tax Obligations**: Moja kompanija → Poreske obaveze
- **Subscription Status**: User Menu → Pretplata

### Common Actions:
- **Download Invoice PDF**: Click invoice → Download icon
- **Cancel Invoice**: Open invoice → Stornirati action
- **Mark Obligation Paid**: Checkbox in obligations list
- **Upload KPO**: KPO knjiga → Otpremi KPO
- **Change Plan**: User Menu → Pretplata → Subscribe button

## Most Common Questions

### "How do I create an invoice?"
→ Go to Fakture → Nova Faktura → Select client → Fill details → Add items → Create

### "How do I add a client?"
→ Go to Klijenti → Novi Klijent → Fill company info → Select type → Create

### "How many invoices can I create?"
→ Free: 3/month | Basic: Unlimited | Grandfather: Unlimited

### "How do I cancel an invoice?"
→ Open invoice → Click "Stornirati" button → System creates storno automatically

### "Where is my annual income shown?"
→ Dashboard → "Prihod tekuće godine" widget shows progress bar

### "How do I upload KPO?"
→ Moja kompanija → KPO knjiga → Otpremi KPO → Select PDF → Upload

### "How do I pay taxes?"
→ System generates QR codes, but payment is through your bank app

### "How do I upgrade subscription?"
→ User Menu → Pretplata → Choose monthly or yearly → Complete checkout

## Key Features Summary

**Invoice Management:**
- Create regular, pro forma, and advance invoices
- Automatic PDF generation
- Storno (cancellation) function
- Multiple currencies (RSD, EUR, USD)
- eFaktura integration

**Client Management:**
- Store unlimited clients
- Domestic and foreign types
- Track all invoices per client
- Auto-complete in invoice forms

**Income Tracking:**
- Automatic from invoices
- Manual entries supported
- Annual limit monitoring
- Dashboard visualizations

**Tax Obligations:**
- Upload tax resolutions
- Auto-extract payment details
- QR code generation
- Payment tracking

**KPO Books:**
- PDF upload and processing
- Auto-extract entries
- Client matching
- Year-based organization

## Status Meanings

**Invoice Statuses:**
- **Izdato** (Issued): Invoice created, not yet paid
- **Naplaćeno** (Charged): Invoice paid
- **Stornirano** (Cancelled): Invoice cancelled with storno

**Obligation Statuses:**
- **Pending**: Not yet paid
- **Paid**: Marked as paid with date

**Subscription Statuses:**
- **Free**: No payment, 3 invoices/month limit
- **Aktivna** (Active): Paid subscription, unlimited
- **Probni period** (Trial): Free trial period
- **Grandfather**: Legacy free unlimited access

## Important Limits

**Free Plan:**
- 3 invoices per month
- Resets on 1st of each month
- All other features unlimited

**File Uploads:**
- KPO: Max 10MB PDF
- Documents: Standard sizes
- Tax Resolutions: PDF format

**Processing Times:**
- Invoice PDF: Instant
- KPO processing: 1-5 minutes
- Tax resolution: 1-2 minutes

## Troubleshooting Quick Fixes

**Can't create invoice:**
1. Check monthly limit
2. Ensure client selected
3. Verify bank account added
4. Refresh page

**PDF not showing:**
1. Wait and refresh
2. Check if invoice saved
3. Try reopening invoice

**Upload not processing:**
1. Check file size (<10MB)
2. Ensure PDF format
3. Wait 5 minutes
4. Refresh page

**Payment failed:**
1. Check card in Stripe
2. Ensure sufficient funds
3. Try different card
4. Stripe auto-retries

## Serbian Terms Translation

| Serbian | English |
|---------|---------|
| Faktura | Invoice |
| Klijent | Client |
| Prihod | Income |
| Obaveza | Obligation |
| PIB | Tax ID |
| Bankovni račun | Bank account |
| Stornirano | Cancelled |
| Naplaćeno | Paid/Charged |
| Izdato | Issued |
| Pretplata | Subscription |

## Response Templates

### For subscription questions:
"You're currently on the [FREE/BASIC] plan. [Details about limits]. To upgrade, go to User Menu → Pretplata → Choose plan → Complete checkout."

### For invoice creation:
"To create an invoice: 1) Go to Fakture 2) Click Nova Faktura 3) Select client 4) Fill details 5) Add items 6) Click Create. The PDF generates automatically."

### For limits:
"On the Free plan, you can create 3 invoices per month. You've used [X] this month. To get unlimited invoices, upgrade to Basic plan for [price]/month."

### For technical issues:
"I understand you're having trouble with [issue]. Try: [solution steps]. If this doesn't work, [alternative]. Still having issues? Please [contact support method]."

## Important URLs

- Dashboard: `/admin`
- Invoices: `/admin/invoices`
- Clients: `/admin/clients`
- Subscription: `/admin/subscription-management`
- Settings: `/admin/sef-efaktura-settings`

## Don't Forget To Mention

✓ PDFs generate automatically
✓ Invoice numbers are automatic
✓ Use storno instead of delete
✓ Mark invoices as paid when received
✓ KPO processing takes time
✓ QR codes for easy payment
✓ eFaktura is optional
✓ Data is always secured
✓ Mobile-friendly interface
✓ 7-day trial on paid plans

---

*Use this guide for quick answers to common questions. For detailed information, refer to the main training guide.*
