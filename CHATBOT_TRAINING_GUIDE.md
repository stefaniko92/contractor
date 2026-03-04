# Pausalci - Chatbot Training Guide

## Application Overview

**Pausalci** is a comprehensive web application designed specifically for Serbian "pausalci" (flat-tax entrepreneurs) to manage their business operations, track income, calculate taxes, create invoices, and monitor payment obligations.

### Target Users
- Serbian flat-tax entrepreneurs (paušalci)
- Small business owners who need to manage:
  - Client relationships
  - Invoice creation and tracking
  - Income monitoring
  - Tax obligations (PIO and Porez)
  - KPO (Knjiga Primljenih Overa) book management

### Key Technology
- Built on Laravel 11+ with Filament v4 Admin Panel
- All text is in Serbian language
- Accessible at `/admin` URL path

---

## Main Features & Modules

### 1. **Dashboard (Kontrolna tabla)**
The main dashboard shows:
- **Welcome Widget**: Greeting and overview
- **Unpaid Invoices (Neplaćene fakture)**: List of invoices awaiting payment
- **Pending Obligations (Obaveze za plaćanje)**: Upcoming tax and contribution payments
- **Current Year Income (Prihod tekuće godine)**: Income statistics and progress toward annual limits
- **12-Month Income (Prihod 12 meseci)**: Rolling 12-month income tracking

Users see their annual income progress and how close they are to paušal limits.

### 2. **Invoicing Module (Fakturisanje)**

#### 2.1 Invoices (Fakture)
- **Purpose**: Create, manage, and track regular invoices
- **Features**:
  - Automatic invoice numbering (e.g., "25/2025")
  - PDF generation for each invoice
  - Status tracking: issued, charged, storno
  - Support for multiple currencies (RSD, EUR, USD)
  - Link invoices to clients
  - Track payment due dates
  - Export to eFaktura system (Serbian government e-invoicing)

**Invoice Types:**
- **Faktura**: Regular invoice
- **Profaktura**: Pro forma invoice (not counted as income)
- **Avansna faktura**: Advance invoice
- **Storno faktura**: Cancellation invoice (negative amount)

#### 2.2 Clients (Klijenti)
- **Purpose**: Manage client/customer database
- **Features**:
  - Store client company information
  - Tax ID (PIB) and registration numbers
  - Contact details (email, phone, address)
  - Client type: domestic (domaći) or foreign (strani)
  - Default place of sale for invoicing
  - Track all invoices per client

**Client Fields:**
- Company name (Naziv kompanije)
- Tax ID / PIB
- Address, city, country
- VAT number (for EU clients)
- Email and phone
- Currency preference

#### 2.3 Pro Forma Invoices (Profakture)
- Similar to regular invoices but marked as "profaktura"
- Used for quotes and preliminary invoicing
- Not counted toward annual income limits

#### 2.4 Advance Invoices (Avansne fakture)
- Invoices for advance payments
- Prefix "A" in invoice number (e.g., "A1/2025")
- Tracked separately from regular invoices

### 3. **My Company Module (Moja kompanija)**

#### 3.1 Company Info (Podaci o kompaniji)
- **Purpose**: Store entrepreneur's business details
- **Fields**:
  - Company name
  - Tax ID (PIB/JMBG)
  - Registration number (Matični broj)
  - Business address
  - City and ZIP code
  - Contact information
  - Business activity code

#### 3.2 Owner Info (Podaci o vlasniku)
- **Purpose**: Personal information about the business owner
- **Fields**:
  - Full name
  - Personal identification number (JMBG)
  - Address
  - Contact details

#### 3.3 Bank Accounts (Bankovni računi)
- **Purpose**: Manage business bank accounts
- **Features**:
  - Add multiple bank accounts
  - Link to specific banks
  - Set default account for invoicing
  - Display account details on invoices

#### 3.4 Documents (Dokumenta)
- **Purpose**: Upload and manage important business documents
- **Features**:
  - Upload PDFs, images, and other file types
  - Categorize documents
  - Secure storage on AWS S3
  - Quick access to important files

#### 3.5 KPO Books (KPO knjiga)
- **Purpose**: Manage KPO (Knjiga Primljenih Overa) - Book of Received Items
- **Features**:
  - Upload KPO PDF files
  - Automatic extraction of entries from PDF
  - View by year with statistics
  - Download compiled KPO books
  - Automatic client matching and creation

**How KPO Upload Works:**
1. User uploads KPO PDF file
2. System processes PDF and extracts all entries
3. System matches clients by name automatically
4. Creates new clients if they don't exist
5. Generates consolidated KPO book

#### 3.6 Incomes (Prihodi)
- **Purpose**: Track all income entries
- **Features**:
  - Linked to invoices or standalone
  - Track payment dates
  - Monitor annual income limits
  - Currency conversion tracking
  - Income statistics and reporting

#### 3.7 Obligations (Zaduženja / Obaveze)
- **Purpose**: Track tax and contribution obligations
- **Types**:
  - **PIO (Penzijsko i Invalidsko Osiguranje)**: Pension and disability insurance
  - **Zdravstveno**: Health insurance
  - **Porez na prihode**: Income tax

**Features:**
- Monthly obligation tracking
- Payment status (pending, paid)
- Due dates and amounts
- Payment reference numbers
- QR code generation for easy payment
- Payment model and reference for bank transfers

#### 3.8 Tax Obligations (Poreske obaveze)
- **Purpose**: Advanced tax obligation management
- **Features**:
  - Upload tax resolutions (rešenja) from Poreska Uprava
  - Automatic PDF processing
  - View obligations by year
  - Generate NBS IPS QR codes for payments
  - Mark obligations as paid
  - Download tax resolution PDFs

**How Tax Resolution Upload Works:**
1. User uploads PDF rešenje from tax authority
2. Selects type (PIO or Porez) and year
3. System processes PDF automatically
4. Extracts obligation details
5. Creates payment records with reference numbers

### 4. **Settings Module (Postavke)**

#### 4.1 SEF/EFaktura Integration
- **Purpose**: Configure integration with Serbian government e-invoicing system
- **Features**:
  - Enable/disable eFaktura integration
  - Store API credentials
  - Set default VAT exemptions
  - Configure webhook URL
  - Automatic invoice submission to government system

**Default VAT Settings:**
- PDV-RS-33 (most common for paušalci)
- PDV-RS-35-7
- PDV-RS-36-5
- PDV-RS-36b-4

#### 4.2 Change Password (Promena lozinke)
- Secure password update functionality

### 5. **Subscription Management (Upravljanje pretplatom)**

#### Subscription Plans:

**Free Plan:**
- Limited to 3 invoices per month
- Access to basic features
- No payment required

**Basic Plan:**
- **Monthly**: Regular monthly billing
- **Yearly**: Annual billing with discount
- Unlimited invoices
- Full access to all features
- 7-day trial period (may be temporarily disabled)

**Grandfather Plan:**
- Legacy plan for early adopters
- Free unlimited access forever
- All features included

**How to Subscribe:**
1. Navigate to "Pretplata" in user menu
2. Choose monthly or yearly plan
3. Click subscribe button
4. Complete Stripe checkout
5. Subscription activates automatically

**Managing Subscription:**
- View current plan and status
- Access Stripe billing portal
- Cancel or modify subscription
- View next billing date
- Download invoices

---

## Navigation Structure

### Top Navigation Groups:

1. **Dashboard (Kontrolna tabla)** - Home page with widgets

2. **Fakturisanje (Invoicing)**
   - Fakture (Invoices)
   - Profakture (Pro Forma Invoices)
   - Avansne fakture (Advance Invoices)
   - Klijenti (Clients)

3. **Moja kompanija (My Company)**
   - Podaci o kompaniji (Company Info)
   - Podaci o vlasniku (Owner Info)
   - Bankovni računi (Bank Accounts)
   - Dokumenta (Documents)
   - KPO knjiga (KPO Books)
   - Prihodi (Incomes)
   - Poreske obaveze (Tax Obligations)

4. **Postavke (Settings)**
   - SEF/EFaktura
   - Promena lozinke (Change Password)

5. **User Menu (Top Right)**
   - Pretplata (Subscription) - shows status badge
   - Odjavi se (Logout)

---

## Step-by-Step User Guides

### How to Create a New Client

1. Click on **"Klijenti"** in the Fakturisanje menu
2. Click **"Novi Klijent"** or "Create" button
3. Fill in required fields:
   - Company name (Naziv kompanije)
   - Tax ID / PIB
   - Address
   - Email (optional)
   - Phone (optional)
4. Select client type:
   - Domaći (Domestic)
   - Strani (Foreign)
5. Choose currency preference (RSD, EUR, USD)
6. Set default place of sale
7. Click **"Kreiraj"** (Create)

### How to Create an Invoice

1. Click on **"Fakture"** in the Fakturisanje menu
2. Click **"Nova Faktura"** or "Create" button
3. Fill in invoice details:
   - Select client from dropdown
   - Select bank account
   - Invoice date (Datum izdavanja)
   - Due date (Datum dospeća)
   - Currency
   - Trading place (Mesto prometa)
4. Add invoice items:
   - Description (Opis)
   - Quantity (Količina)
   - Unit price (Jedinična cena)
   - Total amount (calculated automatically)
5. Review total amount
6. Click **"Kreiraj"** (Create)
7. System automatically:
   - Generates invoice number
   - Creates PDF
   - Adds to income records
   - Updates dashboard statistics

### How to Cancel an Invoice (Storno)

1. Go to **"Fakture"** list
2. Find the invoice to cancel
3. Click on the invoice to edit
4. Click **"Stornirati"** (Cancel) action
5. System automatically:
   - Creates a storno invoice (negative amount)
   - Links to original invoice
   - Marks original as cancelled
   - Adjusts income records
6. Original invoice status changes to "stornirana"

### How to Track Income

1. Click on **"Prihodi"** in Moja kompanija menu
2. View all income entries
3. Income automatically created when:
   - Invoice is marked as "charged" (naplaćeno)
   - Manual income entry is added
4. Filter by:
   - Date range
   - Amount
   - Currency
   - Invoice number
5. View totals and statistics

### How to Upload KPO Book

1. Navigate to **"KPO knjiga"** in Moja kompanija
2. Click **"Otpremi KPO"** button
3. Select PDF file (max 10MB)
4. Click **"Otpremi i Obradi"**
5. Wait for processing (may take several minutes)
6. System will:
   - Extract all entries from PDF
   - Match existing clients by name
   - Create new clients if needed
   - Display processed entries
7. View results organized by year
8. Download compiled KPO book as PDF

### How to Upload Tax Resolution

1. Go to **"Poreske obaveze"** in Moja kompanija
2. Click **"Otpremi Rešenje"** button
3. Select resolution type:
   - DOPRINOS ZA PIO (Pension insurance)
   - POREZ NA PRIHODE (Income tax)
4. Select year
5. Upload PDF file from tax authority
6. Click **"Otpremi i Obradi"**
7. System processes PDF and creates payment obligations
8. View obligations with:
   - Amount to pay
   - Due date
   - Payment reference numbers
   - QR code for easy payment

### How to Pay an Obligation

1. Navigate to **"Poreske obaveze"** or **"Zaduženja"**
2. Find the obligation to pay
3. Option 1: Generate QR code
   - Click QR code icon
   - Scan with mobile banking app
   - Complete payment
4. Option 2: Manual bank transfer
   - Copy payment details
   - Account number
   - Payment code
   - Reference number
   - Amount
5. After payment, mark as paid:
   - Check the "Plaćeno" checkbox
   - Enter payment date
   - System updates status

### How to Configure eFaktura Integration

1. Go to **"Postavke"** → **"SEF/EFaktura"**
2. Toggle **"Omogući SEF/EFaktura"** to ON
3. Enter your API key from SEF/eFaktura service
4. Set default VAT exemption (usually PDV-RS-33)
5. Copy the webhook URL provided
6. Add webhook URL to your SEF/eFaktura account
7. Click **"Sačuvaj postavke"** (Save settings)
8. When creating invoices, they will automatically be sent to eFaktura system

### How to Manage Subscription

1. Click on user menu (top right)
2. Select **"Pretplata"**
3. View current plan status
4. To upgrade:
   - Click **"Pretplati se mesečno"** (Subscribe Monthly)
   - Or **"Pretplati se godišnje"** (Subscribe Yearly)
   - Complete Stripe checkout
5. To manage billing:
   - Click **"Upravljaj naplatom"** (Manage Billing)
   - Opens Stripe portal
   - Update payment method
   - Cancel subscription
   - Download invoices

---

## Important Serbian Business Terms

### Invoice Terms (Faktura)
- **Faktura**: Regular invoice
- **Profaktura**: Pro forma invoice
- **Avansna faktura**: Advance invoice
- **Storno faktura**: Cancellation invoice
- **Broj fakture**: Invoice number
- **Datum izdavanja**: Issue date
- **Datum dospeća**: Due date
- **Izdato**: Issued (status)
- **Naplaćeno**: Charged/Paid (status)
- **Stornirano**: Cancelled (status)
- **Mesto prometa**: Place of trade/sale

### Company Terms (Kompanija)
- **PIB**: Tax identification number (Poreski Identifikacioni Broj)
- **JMBG**: Personal ID number (Jedinstveni Matični Broj Građana)
- **Matični broj**: Registration number
- **Delatnost**: Business activity
- **Domaći klijent**: Domestic client
- **Strani klijent**: Foreign client

### Tax & Obligations (Porezi i Obaveze)
- **PIO**: Pension and disability insurance (Penzijsko i Invalidsko Osiguranje)
- **Zdravstveno**: Health insurance
- **Porez na prihode**: Income tax
- **Poreska uprava**: Tax authority
- **Rešenje**: Tax resolution/decision
- **Obaveza**: Obligation
- **Zaduženje**: Charge/obligation
- **Model**: Payment model (for bank transfers)
- **Poziv na broj**: Payment reference number

### Income Terms (Prihod)
- **Prihod**: Income
- **Godišnji prihod**: Annual income
- **Limit**: Income limit for paušal status
- **Valuta**: Currency
- **Iznos**: Amount

### KPO Terms
- **KPO**: Knjiga Primljenih Overa (Book of Received Items)
- **Unos**: Entry
- **Knjiga**: Book

### Banking Terms
- **Bankovni račun**: Bank account
- **Banka**: Bank
- **IBAN**: International bank account number
- **Račun**: Account/Bill

---

## Common User Questions & Answers

### Q: How do I start using the application?
**A:** After registration:
1. Complete company information in "Podaci o kompaniji"
2. Add owner information in "Podaci o vlasniku"
3. Add at least one bank account in "Bankovni računi"
4. Add your first client in "Klijenti"
5. Create your first invoice in "Fakture"

### Q: How many invoices can I create?
**A:**
- **Free plan**: 3 invoices per month
- **Basic plan**: Unlimited invoices
- **Grandfather plan**: Unlimited invoices

### Q: What happens when I reach the invoice limit?
**A:** On the free plan, after 3 invoices in a month, you'll need to either:
- Wait until next month
- Upgrade to Basic plan for unlimited invoices

### Q: How do I cancel an invoice?
**A:** Open the invoice and click "Stornirati" action. The system will create a storno invoice automatically.

### Q: Can I delete an invoice?
**A:** You can delete invoices, but it's better to use the storno function to maintain proper records and audit trail.

### Q: How does automatic invoice numbering work?
**A:** The system automatically generates invoice numbers in format:
- Regular invoices: "1/2025", "2/2025", etc.
- Advance invoices: "A1/2025", "A2/2025", etc.
- Pro forma: "P1/2025", "P2/2025", etc.
The number increases automatically per year and per type.

### Q: Can I edit an invoice after creating it?
**A:** Yes, you can edit invoices before they are marked as paid. Click on the invoice to open the edit page.

### Q: How do I generate a PDF invoice?
**A:** PDFs are generated automatically when you create an invoice. You can download them from the invoice list or detail page.

### Q: What is eFaktura integration?
**A:** eFaktura is the Serbian government's electronic invoicing system. When enabled, your invoices are automatically submitted to the government database, making it easier for B2G (business to government) transactions.

### Q: How do I track my annual income limit?
**A:** The dashboard shows your current year income with a progress bar indicating how close you are to the paušal income limit. The widget "Prihod tekuće godine" displays this information.

### Q: What is KPO and why do I need it?
**A:** KPO (Knjiga Primljenih Overa) is a legally required book for tracking all received items and income. Serbian entrepreneurs must maintain this book and submit it periodically. The system helps you generate and manage it automatically.

### Q: How do I upload my tax resolution?
**A:** Go to "Poreske obaveze" → "Otpremi Rešenje" → Select type and year → Upload PDF. The system will process it and create payment obligations automatically.

### Q: Can I pay my obligations directly from the app?
**A:** The app generates QR codes and provides payment details, but actual payment happens through your bank. After paying, mark the obligation as paid in the system.

### Q: How do I change my subscription?
**A:** Go to user menu → "Pretplata" → "Upravljaj naplatom". This opens the Stripe portal where you can change plans, update payment methods, or cancel.

### Q: What currencies are supported?
**A:** The system supports RSD (Serbian Dinar), EUR (Euro), and USD (US Dollar). You can create invoices in any of these currencies.

### Q: How do I add a new bank account?
**A:** Go to "Moja kompanija" → "Bankovni računi" → Click "Novi" → Fill in details → Save.

### Q: Can I have multiple clients with the same name?
**A:** Yes, but it's better to use unique identifiers like adding city or using full legal names to avoid confusion.

### Q: How does the system match clients when uploading KPO?
**A:** The system matches clients by company name. If an exact match is found, it uses that client. Otherwise, it creates a new client automatically.

### Q: What happens if I don't pay my subscription?
**A:** If payment fails:
- Stripe will retry automatically
- You'll receive email notifications
- After several failed attempts, subscription may be cancelled
- Your account reverts to Free plan (3 invoices/month)

### Q: Can I export my data?
**A:** Currently, you can:
- Download individual invoice PDFs
- Download KPO books as PDF
- Download tax resolutions
Full data export feature may be added in future updates.

### Q: Is my data secure?
**A:** Yes. The system uses:
- Encrypted connections (HTTPS)
- Secure AWS S3 storage for files
- User authentication and authorization
- Data isolation (you only see your own data)

### Q: Can I use this application on mobile?
**A:** Yes! The interface is responsive and works on mobile devices, tablets, and desktop computers.

### Q: How do I get support?
**A:** You can use this chat support system, or contact the support team through the contact information provided in your account.

---

## Subscription & Billing

### Free Plan Details:
- Cost: FREE
- Invoice limit: 3 per month
- All basic features included
- No credit card required

### Basic Monthly Plan:
- Cost: (Check current pricing in app)
- Billing: Monthly
- Unlimited invoices
- All features included
- Cancel anytime

### Basic Yearly Plan:
- Cost: (Check current pricing in app)
- Billing: Annually
- Unlimited invoices
- Discount compared to monthly
- All features included
- Cancel anytime

### Grandfather Plan:
- Cost: FREE forever
- For early adopters only
- Unlimited everything
- All features included
- No expiration

### Payment Methods:
- Credit/Debit cards (via Stripe)
- Secure checkout process
- Automatic billing

### Billing Portal:
Access through: User Menu → Pretplata → Upravljaj naplatom
Features:
- Update payment method
- View invoice history
- Download receipts
- Cancel subscription
- Change plan

---

## Troubleshooting

### Issue: Can't create invoice
**Solutions:**
1. Check if you have reached monthly limit (Free plan)
2. Ensure client is selected
3. Verify bank account is added
4. Check that all required fields are filled
5. Try refreshing the page

### Issue: PDF not generating
**Solutions:**
1. Wait a few moments and refresh
2. Check if invoice was saved successfully
3. Try opening invoice again
4. Contact support if persists

### Issue: KPO upload not processing
**Solutions:**
1. Ensure PDF is under 10MB
2. Check PDF is not corrupted
3. Wait at least 5 minutes for processing
4. Refresh page to see updated status
5. Check if file format is correct (PDF only)

### Issue: eFaktura integration not working
**Solutions:**
1. Verify API key is correct
2. Check if integration is enabled
3. Ensure default VAT exemption is set
4. Contact SEF/eFaktura support for API issues
5. Check webhook URL is configured correctly

### Issue: Can't mark obligation as paid
**Solutions:**
1. Verify you're logged in
2. Check if obligation belongs to your account
3. Try refreshing the page
4. Ensure payment date is valid

### Issue: Dashboard not loading
**Solutions:**
1. Refresh the browser
2. Clear browser cache
3. Check internet connection
4. Try different browser
5. Contact support

### Issue: Subscription payment failed
**Solutions:**
1. Check card details in Stripe portal
2. Ensure sufficient funds
3. Try different payment method
4. Contact your bank
5. Stripe will retry automatically

---

## Best Practices

### Invoice Management:
1. **Use storno instead of delete** for cancelled invoices
2. **Fill in all client details** for professional invoices
3. **Set realistic due dates** (typically 7-30 days)
4. **Use consistent descriptions** for similar services
5. **Download PDFs regularly** for backup

### Client Management:
1. **Keep PIB/Tax IDs updated** for accurate records
2. **Use full legal names** not abbreviations
3. **Store complete contact information** for easy communication
4. **Mark client type correctly** (domestic vs foreign) for tax purposes
5. **Set default currency** for regular clients

### Income Tracking:
1. **Review dashboard weekly** to track progress
2. **Monitor annual limit** to maintain paušal status
3. **Mark invoices as paid** when payment received
4. **Record payment dates accurately** for tax purposes
5. **Check monthly totals** against bank statements

### Tax Obligations:
1. **Upload tax resolutions promptly** when received
2. **Pay obligations before due date** to avoid penalties
3. **Mark as paid immediately** after payment for accurate records
4. **Keep payment confirmations** from bank
5. **Review quarterly** to ensure all obligations are paid

### Data Security:
1. **Use strong password** for your account
2. **Don't share login credentials**
3. **Log out on shared computers**
4. **Regularly backup important PDFs**
5. **Keep eFaktura API key secure**

---

## Technical Notes

### Browser Compatibility:
- Chrome (recommended)
- Firefox
- Safari
- Edge
- Modern mobile browsers

### File Upload Limits:
- KPO PDFs: 10MB maximum
- Tax resolutions: Standard PDF size
- Documents: Varies by type

### Data Processing Times:
- Invoice PDF generation: Instant
- KPO upload processing: 1-5 minutes
- Tax resolution processing: 1-2 minutes
- eFaktura submission: Few seconds

### Session Management:
- Auto-logout after inactivity
- Secure session handling
- Remember me option available

---

## Glossary of All Terms

**Avansna faktura**: Advance invoice
**Bankovni račun**: Bank account
**Datum dospeća**: Due date
**Datum izdavanja**: Issue date
**Delatnost**: Business activity
**Domaći klijent**: Domestic client
**eFaktura**: Electronic invoicing system (government)
**Faktura**: Invoice
**IBAN**: International Bank Account Number
**Iznos**: Amount
**JMBG**: Personal ID number
**Klijent**: Client
**Knjiga**: Book
**KPO**: Book of Received Items
**Matični broj**: Registration number
**Mesto prometa**: Place of trade
**Model**: Payment model
**Naplaćeno**: Charged/Paid
**Obaveza**: Obligation
**PIB**: Tax identification number
**PIO**: Pension and disability insurance
**Porez na prihode**: Income tax
**Poreska uprava**: Tax authority
**Poziv na broj**: Payment reference
**Pretplata**: Subscription
**Prihod**: Income
**Profaktura**: Pro forma invoice
**Račun**: Account
**Rešenje**: Tax resolution
**Stornirano**: Cancelled
**Storno faktura**: Cancellation invoice
**Strani klijent**: Foreign client
**Unos**: Entry
**Valuta**: Currency
**Zaduženje**: Obligation/Charge
**Zdravstveno**: Health insurance

---

## Contact & Support

For technical issues, subscription questions, or feature requests:
- Use this chatbot support
- Email support (check in-app for current contact)
- Response time: Usually within 24-48 hours

For urgent issues:
- Payment/billing problems
- Cannot access account
- Data loss concerns

Priority support available for paid subscribers.

---

*This guide covers the main features and workflows of the Pausalci application. For specific technical questions or feature requests, please use the chat support.*

**Last Updated**: 2025-03-04
**App Version**: Laravel 12 / Filament 4
