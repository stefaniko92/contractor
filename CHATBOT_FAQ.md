# Pausalci - Frequently Asked Questions (FAQ)

## Getting Started

### Q1: What is Pausalci and who is it for?
**A:** Pausalci is a business management application designed specifically for Serbian flat-tax entrepreneurs (paušalci). It helps you:
- Create and manage invoices
- Track income and monitor annual limits
- Manage tax obligations
- Maintain KPO books
- Store client information
- Generate required reports

Perfect for Serbian entrepreneurs who need to stay compliant with tax regulations while managing their business efficiently.

---

### Q2: How do I get started with Pausalci?
**A:** Follow these steps after registration:

1. **Complete Company Info**: Go to Moja kompanija → Podaci o kompaniji
   - Add your company name, PIB, address
   - Fill in business activity details

2. **Add Owner Info**: Go to Podaci o vlasniku
   - Add personal information (JMBG, name, address)

3. **Set Up Bank Account**: Go to Bankovni računi
   - Add at least one business bank account
   - This is required for invoicing

4. **Add Your First Client**: Go to Fakturisanje → Klijenti
   - Create a client record
   - Store their company details

5. **Create Your First Invoice**: Go to Fakturisanje → Fakture
   - Click Nova Faktura
   - Select client, add items, create

You're now ready to use all features!

---

### Q3: Do I need to be a Serbian entrepreneur to use this?
**A:** The application is designed specifically for Serbian flat-tax entrepreneurs (paušalci) who need to comply with Serbian tax regulations. While others could technically use it for invoicing, many features (KPO, tax obligations, eFaktura) are Serbia-specific.

---

## Invoices & Billing

### Q4: How do I create an invoice?
**A:** Creating an invoice is straightforward:

1. Navigate to **Fakturisanje** → **Fakture**
2. Click **Nova Faktura** (New Invoice) button
3. Fill in the required fields:
   - **Client**: Select from dropdown (must exist first)
   - **Bank Account**: Choose your bank account
   - **Issue Date**: When invoice is created
   - **Due Date**: Payment deadline
   - **Currency**: RSD, EUR, or USD
   - **Trading Place**: Location of trade
4. Add invoice items:
   - **Description**: What you're billing for
   - **Quantity**: How many units
   - **Unit Price**: Price per unit
   - Total calculates automatically
5. Review total amount
6. Click **Kreiraj** (Create)

The system will:
- Generate an invoice number automatically
- Create a PDF document
- Add to your income records
- Update your dashboard statistics

---

### Q5: How does invoice numbering work?
**A:** Invoice numbers are generated automatically based on:
- Document type
- Year
- Sequential number

**Format examples:**
- Regular invoice: `1/2025`, `2/2025`, `3/2025`...
- Advance invoice: `A1/2025`, `A2/2025`, `A3/2025`...
- Pro forma: `P1/2025`, `P2/2025`, `P3/2025`...

The system:
- Tracks the highest number per type and year
- Increments automatically
- Resets annually
- Prevents duplicates

You can manually override if needed, but automatic numbering is recommended for consistency.

---

### Q6: Can I edit an invoice after creating it?
**A:** Yes! You can edit invoices that haven't been marked as paid.

**To edit:**
1. Go to **Fakture** list
2. Click on the invoice
3. Make your changes
4. Click **Sačuvaj** (Save)

**Important notes:**
- Once marked as "Naplaćeno" (Paid), editing is limited
- If you need to cancel a paid invoice, use the **Storno** function
- Invoice number cannot be changed after creation
- PDFs regenerate automatically when you save changes

---

### Q7: What's the difference between Faktura, Profaktura, and Avansna faktura?
**A:**

**Faktura (Regular Invoice):**
- Standard invoice for completed services/products
- Counts toward annual income
- Triggers income record when created
- Used for actual billing

**Profaktura (Pro Forma Invoice):**
- Quote or preliminary invoice
- Does NOT count toward annual income
- Used before actual delivery
- Can be converted to regular invoice later

**Avansna Faktura (Advance Invoice):**
- Invoice for advance/deposit payments
- Counts toward income
- Used when receiving payment upfront
- Prefix "A" in invoice number

---

### Q8: How do I cancel an invoice?
**A:** Use the **Storno** function instead of deleting:

**Steps:**
1. Open the invoice you want to cancel
2. Click the **Stornirati** (Cancel) action button
3. System automatically:
   - Creates a storno invoice (negative amount)
   - Links it to original invoice
   - Updates original status to "Stornirano"
   - Adjusts income records
   - Updates dashboard statistics

**Why use Storno instead of Delete?**
- Maintains complete audit trail
- Legally compliant accounting practice
- Shows full history of transactions
- Required for tax purposes

---

### Q9: Can I create invoices in different currencies?
**A:** Yes! The system supports:
- **RSD** (Serbian Dinar) - Primary currency
- **EUR** (Euro) - Common for foreign clients
- **USD** (US Dollar) - For international work

**To use different currencies:**
1. When creating invoice, select currency from dropdown
2. Enter amounts in that currency
3. PDF and records show the selected currency
4. Income tracking converts to RSD for annual limits

**Note:** Exchange rates for income calculation may need to be updated manually or set in system.

---

### Q10: Where do I download invoice PDFs?
**A:** PDFs are generated automatically:

**Method 1 - From List:**
1. Go to **Fakture** list
2. Find your invoice
3. Click download icon (⬇️) in the row

**Method 2 - From Detail:**
1. Click on invoice to open it
2. Look for download button in header
3. Click to download PDF

**Method 3 - Email:**
- You can also email PDFs directly to clients (if feature enabled)

PDFs include:
- All invoice details
- Your company information
- Client information
- Itemized list
- Total amounts
- Payment instructions

---

## Clients

### Q11: How do I add a new client?
**A:** Adding clients is simple:

1. Go to **Fakturisanje** → **Klijenti**
2. Click **Novi Klijent** (New Client)
3. Fill in required information:
   - **Company Name**: Full legal name
   - **PIB** (Tax ID): Required for Serbian clients
   - **Address**: Full street address
   - **City**: Client's city
   - **Country**: Important for tax purposes
   - **Email**: For sending invoices (optional)
   - **Phone**: Contact number (optional)
4. Select **Client Type**:
   - **Domaći** (Domestic): Serbian clients
   - **Strani** (Foreign): International clients
5. Choose **Currency**: Default currency for invoices
6. Set **Default Place of Sale**: For invoice generation
7. Add **Notes**: Any internal notes (optional)
8. Click **Kreiraj** (Create)

**Tips:**
- Use full legal names for accuracy
- Always include PIB for Serbian clients
- Store complete contact info for easy communication
- Set currency preference for regular clients

---

### Q12: Can I have clients from other countries?
**A:** Yes! You can add foreign clients:

1. When creating/editing client
2. Select **Strani klijent** (Foreign client)
3. Fill in their country
4. Add VAT number if EU client
5. Choose their preferred currency (EUR/USD)

**For foreign clients:**
- Invoice generation adapts automatically
- Currency can be EUR or USD
- VAT rules may differ (consult accountant)
- Place of sale affects tax treatment

---

### Q13: Can I edit or delete client information?
**A:** Yes, you can manage clients easily:

**To Edit:**
1. Go to **Klijenti** list
2. Click on client name
3. Update any information
4. Click **Sačuvaj** (Save)

**To Delete:**
1. Go to **Klijenti** list
2. Select client
3. Click delete action
4. Confirm deletion

**Important:**
- Cannot delete clients with existing invoices
- Consider deactivating instead if they have history
- Deleting removes all data permanently
- Export data first if needed for records

---

## Income & Tracking

### Q14: How is my income tracked?
**A:** Income is tracked automatically and manually:

**Automatic Tracking:**
- When you create an invoice, system adds it to income
- Linked to the invoice for reference
- Shows on dashboard immediately
- Counts toward annual limits

**Manual Income:**
- Go to **Moja kompanija** → **Prihodi**
- Click **Novi** to add income without invoice
- Enter amount, date, description
- Useful for cash payments or other income

**Dashboard Shows:**
- Current year total
- 12-month rolling total
- Progress toward paušal limits
- Monthly breakdown
- Visual charts

---

### Q15: What are the paušal income limits?
**A:** As of current Serbian tax law, paušalci have annual income limits above which you must switch to regular tax system.

**The limits vary by:**
- Municipality
- Business activity type
- Number of employees
- Current year regulations

**In the app:**
- Dashboard shows your progress
- Visual indicator shows percentage used
- Warning when approaching limit
- Annual reset on January 1st

**Important:** Always consult with your accountant or tax advisor about current limits for your specific situation.

---

### Q16: How do I see my income by month or year?
**A:** Multiple ways to view income:

**Dashboard View:**
- Shows current year total with chart
- 12-month rolling view
- Visual progress bars

**Prihodi List:**
1. Go to **Moja kompanija** → **Prihodi**
2. Use date filters to narrow down
3. Filter by:
   - Date range
   - Amount
   - Currency
   - Invoice number
4. Export or print reports

**KPO Books:**
1. Go to **KPO knjiga**
2. View organized by year
3. See total income per year
4. Download annual PDFs

---

## Tax Obligations

### Q17: What are tax obligations and how do I manage them?
**A:** Tax obligations are your required payments to Serbian tax authority:

**Types of Obligations:**
1. **PIO** (Penzijsko i Invalidsko Osiguranje)
   - Pension and disability insurance
   - Monthly or quarterly
   - Fixed amount from tax resolution

2. **Zdravstveno** (Health Insurance)
   - Health insurance contribution
   - Monthly payments
   - Amount from resolution

3. **Porez na prihode** (Income Tax)
   - Tax on your income
   - Amount determined by tax authority
   - Based on your municipality and activity

**How to Manage:**
1. Receive tax resolution from Poreska Uprava
2. Upload PDF to system (Poreske obaveze → Otpremi Rešenje)
3. System extracts payment details automatically
4. View all obligations with dates and amounts
5. Generate QR codes for easy payment
6. Mark as paid after payment

---

### Q18: How do I upload my tax resolution?
**A:** Follow these steps:

1. **Get Your Resolution**:
   - Receive PDF from Poreska Uprava (Tax Authority)
   - Can be via email or downloaded from portal

2. **Upload to System**:
   - Go to **Moja kompanija** → **Poreske obaveze**
   - Click **Otpremi Rešenje** (Upload Resolution)
   - Select type:
     - **DOPRINOS ZA PIO** (Pension insurance)
     - **POREZ NA PRIHODE** (Income tax)
   - Select year
   - Upload PDF file
   - Click **Otpremi i Obradi** (Upload and Process)

3. **System Processing**:
   - Extracts all payment details (1-2 minutes)
   - Creates obligation records
   - Sets due dates
   - Adds payment reference numbers
   - Generates payment codes

4. **View Results**:
   - See all obligations listed
   - Monthly breakdown
   - Payment details for each
   - QR codes available

---

### Q19: How do I pay my tax obligations?
**A:** The app helps you prepare for payment:

**Option 1 - QR Code Payment:**
1. Open **Poreske obaveze**
2. Find obligation to pay
3. Click QR code icon
4. Scan with mobile banking app
5. Confirm payment in banking app
6. Return to Pausalci and mark as paid

**Option 2 - Manual Bank Transfer:**
1. View obligation details
2. Copy payment information:
   - Recipient account number
   - Recipient name
   - Amount
   - Payment code
   - Model number
   - Reference number
3. Enter in online banking
4. Complete transfer
5. Mark as paid in Pausalci

**After Payment:**
1. Check obligation in system
2. Click checkbox or edit
3. Select **Plaćeno** (Paid)
4. Enter payment date
5. Save

**Note:** Actual payment happens through your bank - the app provides the details you need and helps you track what's paid.

---

### Q20: What is a QR code for payment?
**A:** QR codes are quick payment codes:

**What it contains:**
- Recipient's bank account
- Recipient name (Tax Authority, PIO Fund, etc.)
- Exact amount to pay
- Payment code
- Reference numbers
- All required payment details

**How to use:**
1. Open Pausalci app
2. Generate QR code for obligation
3. Open your bank's mobile app
4. Use "Scan to pay" or "QR payment" feature
5. Scan code
6. Review details (pre-filled)
7. Confirm payment

**Benefits:**
- No manual typing
- No errors in reference numbers
- Fast payment
- All details correct automatically

---

## KPO Books

### Q21: What is KPO and why do I need it?
**A:** KPO stands for **Knjiga Primljenih Overa** (Book of Received Items).

**What it is:**
- Legal requirement for Serbian entrepreneurs
- Records all income-generating transactions
- Must be maintained continuously
- Submitted periodically to tax authority

**Why you need it:**
- **Legally required** by Serbian law
- **Tax compliance** - Proves your income
- **Audit trail** - Shows all business activity
- **Annual reporting** - Used for tax returns

**What Pausalci does:**
- Generates KPO automatically from invoices
- Accepts PDF uploads from accountants
- Organizes by year
- Creates printable books
- Ensures compliance

---

### Q22: How do I upload a KPO book?
**A:** Simple upload process:

1. **Prepare Your PDF**:
   - Get KPO PDF (from accountant or tax portal)
   - Ensure it's under 10MB
   - Verify PDF opens correctly

2. **Upload**:
   - Go to **Moja kompanija** → **KPO knjiga**
   - Click **Otpremi KPO** (Upload KPO)
   - Select PDF file
   - Click **Otpremi i Obradi** (Upload and Process)

3. **Processing** (1-5 minutes):
   - System reads PDF
   - Extracts all entries
   - Matches clients by name
   - Creates new clients if needed
   - Organizes by date

4. **Review Results**:
   - View extracted entries
   - Check client matching
   - Verify amounts
   - Organized by year

**What happens to data:**
- Stored securely on AWS S3
- Linked to your account
- Accessible anytime
- Included in reports

---

### Q23: Can I download my KPO book?
**A:** Yes! Multiple download options:

**Annual KPO:**
1. Go to **KPO knjiga**
2. See years listed with statistics
3. Click download icon for year
4. Downloads PDF of entire year

**Individual Invoices:**
- Each invoice has own PDF
- Download from Fakture list

**Exports:**
- Consider export features (if available)
- Check with support for bulk downloads

**Use cases:**
- Submit to tax authority
- Give to accountant
- Keep for records
- Annual archiving

---

## Subscriptions & Billing

### Q24: What subscription plans are available?
**A:** Three plans to choose from:

**FREE PLAN** - €0/month
- **3 invoices per month**
- All basic features
- Client management
- Income tracking
- Tax obligation tracking
- KPO uploads
- Perfect for: Testing the app or very light usage

**BASIC MONTHLY** - (Check current price in app)
- **Unlimited invoices**
- All features included
- No limits on clients
- Priority support
- Monthly billing
- Cancel anytime
- Perfect for: Regular business activity

**BASIC YEARLY** - (Check current price in app)
- **Unlimited invoices**
- All features included
- **Save compared to monthly**
- Annual billing
- Cancel anytime
- Perfect for: Committed users wanting savings

**GRANDFATHER PLAN**
- **Free unlimited forever**
- Early adopter benefit
- All features included
- No expiration
- Not available for new signups

---

### Q25: How do I upgrade my subscription?
**A:** Upgrading is quick and easy:

1. **Access Subscription**:
   - Click user menu (top right)
   - Select **Pretplata** (Subscription)

2. **View Current Plan**:
   - See your current status
   - Review plan limits
   - Check invoice usage

3. **Choose New Plan**:
   - Click **Pretplati se mesečno** (Subscribe Monthly)
   - OR **Pretplati se godišnje** (Subscribe Yearly)

4. **Complete Checkout**:
   - Redirects to Stripe
   - Enter payment details
   - Confirm subscription
   - Return to app

5. **Activation**:
   - Subscription activates immediately
   - Limits removed
   - Full access to all features

**Payment Methods:**
- Credit/Debit cards
- Secure through Stripe
- No payment info stored on our servers

---

### Q26: What happens if I reach my invoice limit on Free plan?
**A:** When you create 3 invoices in a month on Free plan:

**You'll see:**
- Warning message
- Current usage (3/3)
- Options to proceed

**Your Options:**

1. **Wait Until Next Month**:
   - Limit resets on 1st of month
   - Can create 3 more invoices
   - All other features still work

2. **Upgrade to Basic Plan**:
   - Unlimited invoices immediately
   - Click upgrade button
   - Complete Stripe checkout
   - Start creating more invoices

**What Still Works:**
- Viewing existing invoices
- Managing clients
- Tracking income
- Tax obligations
- KPO books
- All data access

**What's Limited:**
- Creating NEW invoices
- Only restriction on Free plan

---

### Q27: How do I cancel my subscription?
**A:** You can cancel anytime:

1. **Open Billing Portal**:
   - User menu → **Pretplata**
   - Click **Upravljaj naplatom** (Manage Billing)
   - Opens Stripe portal

2. **Cancel Subscription**:
   - Find subscription section
   - Click cancel option
   - Confirm cancellation
   - Choose reason (optional)

3. **What Happens**:
   - Access continues until period end
   - No more charges after current period
   - Reverts to Free plan automatically
   - All data remains accessible

4. **Free Plan Limits Apply**:
   - 3 invoices per month from next month
   - All other features still available

**Notes:**
- No penalties for cancelling
- Re-subscribe anytime
- Data is never deleted
- No questions asked

---

### Q28: How do I update my payment method?
**A:** Update payment details through Stripe:

1. User menu → **Pretplata** → **Upravljaj naplatom**
2. Opens Stripe billing portal
3. Find **Payment Methods** section
4. Click **Add Payment Method**
5. Enter new card details
6. Set as default
7. Remove old card if desired

**Or:**
- Update during failed payment
- Stripe emails link if payment fails
- Click link to update and retry

**Security:**
- All card info handled by Stripe
- PCI compliant
- Never stored on our servers
- Secure encryption

---

## Settings & Configuration

### Q29: What is eFaktura integration?
**A:** eFaktura is Serbia's government electronic invoicing system:

**What it does:**
- Submits invoices to government database
- Required for B2G (business-to-government) transactions
- Optional for B2B (business-to-business)
- Automatically validates invoice data

**Pausalci Integration:**
- **Enable in Settings**: Postavke → SEF/EFaktura
- **Add API Key**: From your eFaktura account
- **Auto-submit**: Invoices sent automatically
- **Track Status**: See submission status
- **Webhooks**: Get notifications on changes

**Benefits:**
- Faster government payments
- Automatic validation
- Compliance with regulations
- No manual portal entry

**Setup Required:**
1. Register with SEF/eFaktura service
2. Get API credentials
3. Configure in Pausalci settings
4. Set default VAT exemptions
5. Test with sample invoice

---

### Q30: How do I configure eFaktura?
**A:** Step-by-step configuration:

1. **Register with eFaktura**:
   - Go to official eFaktura website
   - Create account
   - Get API credentials

2. **Enable in Pausalci**:
   - **Postavke** → **SEF/EFaktura**
   - Toggle **Omogući SEF/EFaktura** to ON

3. **Enter API Key**:
   - Paste your API key
   - System validates connection

4. **Set Defaults**:
   - **VAT Exemption**: Usually PDV-RS-33 for paušalci
   - **VAT Category**: SS (auto-set)

5. **Configure Webhook**:
   - Copy webhook URL shown
   - Add to your eFaktura account
   - Receives status updates

6. **Save Settings**:
   - Click **Sačuvaj postavke**
   - Test with new invoice

**After Setup:**
- New invoices auto-submit
- Status shows in invoice list
- Errors displayed if any
- Can retry failed submissions

---

## Troubleshooting

### Q31: I can't create an invoice. What's wrong?
**A:** Common causes and solutions:

**Check 1 - Invoice Limit**:
- Free plan: 3 invoices/month
- Solution: Upgrade to Basic plan

**Check 2 - Client Not Selected**:
- Must choose client from dropdown
- Solution: Add client first, then create invoice

**Check 3 - No Bank Account**:
- Requires at least one bank account
- Solution: Add bank account in Bankovni računi

**Check 4 - Missing Required Fields**:
- All required fields must be filled
- Look for red indicators
- Solution: Fill in all mandatory fields

**Check 5 - Technical Issue**:
- Browser problem or timeout
- Solution: Refresh page and try again

**Still Not Working?**
- Clear browser cache
- Try different browser
- Contact support with error message

---

### Q32: The invoice PDF won't load or download.
**A:** Try these solutions:

**Quick Fixes:**
1. **Refresh the page** - Wait 10 seconds, try again
2. **Check if saved** - Look for success message
3. **Reopen invoice** - Go to list, click invoice again
4. **Different browser** - Try Chrome or Firefox
5. **Clear cache** - May have old version cached

**If Still Not Working:**
- **Server Issue**: Wait 5 minutes, retry
- **PDF Generation Failed**: Edit invoice, save again
- **File Size**: Very large invoices may take time

**Technical Issues:**
- Contact support
- Provide invoice number
- Mention any error messages
- They can regenerate PDF manually

**Workaround:**
- Can recreate invoice if necessary
- Export invoice data
- Generate locally if urgent

---

### Q33: My KPO upload is stuck or failed.
**A:** Troubleshooting KPO uploads:

**Normal Processing Time:**
- Small files (<1MB): 1-2 minutes
- Medium files (1-5MB): 2-5 minutes
- Large files (5-10MB): 5-10 minutes

**If Stuck:**

1. **Wait Longer**:
   - Processing happens in background
   - Refresh after 5 minutes
   - Check status indicator

2. **Check File**:
   - Must be PDF format
   - Under 10MB size limit
   - Valid, not corrupted PDF
   - Opens normally on computer

3. **Try Again**:
   - Delete stuck upload (if option available)
   - Re-save PDF from source
   - Upload fresh copy

4. **File Issues**:
   - Scanned PDFs may have issues
   - Try OCR if scanned
   - Ensure text is readable
   - Not password protected

**Still Failing?**
- Contact support with:
  - File size
  - Upload ID (if shown)
  - Error message
  - Time of upload
- Support can check server logs

---

### Q34: I forgot my password. How do I reset it?
**A:** Password reset is simple:

1. Go to login page (`/admin`)
2. Click **Zaboravili ste lozinku?** (Forgot password?)
3. Enter your email address
4. Click **Pošalji link** (Send link)
5. Check email inbox
6. Click reset link in email
7. Enter new password (twice)
8. Click **Resetuj lozinku** (Reset password)
9. Log in with new password

**Didn't Receive Email?**
- Check spam/junk folder
- Wait 5-10 minutes
- Try again with correct email
- Contact support if still not received

**Security:**
- Reset links expire after 60 minutes
- One-time use only
- Requires email access
- Old password immediately invalidated

---

### Q35: Can I change my email address?
**A:** Email changes require support assistance:

**Process:**
1. Contact support
2. Provide:
   - Current email
   - New email address
   - Account verification info
3. Support verifies identity
4. Changes email in system
5. Confirmation sent to both emails

**Why Support Only?**
- Security measure
- Prevents unauthorized changes
- Ensures account ownership
- Updates all billing info

**Same Process For:**
- Primary contact email
- Billing email
- Notification preferences

---

## Data & Security

### Q36: Is my data secure?
**A:** Yes! Multiple security measures:

**Data Encryption:**
- HTTPS connections (SSL/TLS)
- Data encrypted in transit
- Secure storage on AWS S3
- Industry-standard encryption

**Access Control:**
- User authentication required
- Password hashing (bcrypt)
- Session management
- Secure cookies

**Data Isolation:**
- You only see your data
- User ID verification
- No cross-account access
- Role-based permissions

**Backups:**
- Regular automated backups
- Disaster recovery plans
- Multiple geographic locations
- Point-in-time recovery

**Compliance:**
- GDPR considerations
- Serbian data laws
- Secure payment (Stripe PCI)
- Audit trails

---

### Q37: Can I export my data?
**A:** Current export options:

**Available Now:**
- **Invoice PDFs**: Individual downloads
- **KPO Books**: Annual PDF exports
- **Tax Resolutions**: PDF downloads

**Limited Exports:**
- Client lists (manual copy)
- Income reports (screen data)
- Obligation lists (screen data)

**Requested for Future:**
- Full data export (CSV/JSON)
- Bulk PDF download
- Accounting software integration
- API access

**Current Workaround:**
- Manually download important PDFs
- Screenshot reports as needed
- Contact support for special exports

---

### Q38: What happens if I delete my account?
**A:** Account deletion is permanent:

**Process:**
1. Contact support (no self-service deletion)
2. Request account deletion
3. Confirm identity
4. Download any needed data first
5. Account deleted within 7 days

**What Gets Deleted:**
- All invoices and PDFs
- Client records
- Income data
- Tax obligations
- KPO uploads
- All uploaded documents
- User account

**Cannot Be Undone:**
- Deletion is permanent
- No recovery after 30 days
- All data removed completely

**Before Deleting:**
- Export all needed data
- Download important PDFs
- Save client information
- Note any pending obligations
- Cancel subscription first

---

## Technical Questions

### Q39: Which browsers are supported?
**A:** Pausalci works on modern browsers:

**Fully Supported:**
- **Chrome** (recommended) - Version 90+
- **Firefox** - Version 88+
- **Safari** - Version 14+
- **Edge** - Version 90+

**Mobile Browsers:**
- Chrome Mobile
- Safari iOS
- Samsung Internet
- Firefox Mobile

**Not Supported:**
- Internet Explorer (all versions)
- Very old browser versions
- Browsers with JavaScript disabled

**Best Experience:**
- Use latest browser version
- Enable JavaScript
- Allow cookies
- Disable aggressive ad blockers (may interfere)

**Responsive Design:**
- Works on desktop
- Works on tablets
- Works on smartphones
- Adapts to screen size

---

### Q40: Can I use Pausalci on my phone?
**A:** Yes! Fully mobile-responsive:

**Mobile Features:**
- Create invoices
- View client list
- Check income
- Download PDFs
- Scan QR codes
- Upload photos/documents
- Full access to all features

**Optimized For:**
- Touch navigation
- Small screens
- Mobile keyboards
- Portrait/landscape
- Swipe gestures

**Best Practices:**
- Use mobile browser (not needed app)
- Chrome or Safari recommended
- Stable internet connection
- Save bookmark for quick access

**Limitations:**
- Large PDFs may be slow
- Better to create complex invoices on desktop
- File uploads easier from computer

---

## Support & Contact

### Q41: How do I get help if I have a problem?
**A:** Multiple support options:

**This Chatbot:**
- Available 24/7
- Instant answers
- Common questions
- How-to guides
- Use for quick help

**Email Support:**
- Check in-app for contact email
- Response within 24-48 hours
- Detailed technical help
- Account-specific issues

**Documentation:**
- User guides
- FAQ
- Video tutorials (if available)
- Knowledge base

**What to Include:**
- Clear description of problem
- What you were trying to do
- Error messages (screenshots)
- Browser and device info
- Account email

---

### Q42: Is there a user manual or documentation?
**A:** Documentation available:

**In-App Help:**
- Tooltips and hints
- Helper text in forms
- Success/error messages
- Contextual guidance

**Online Resources:**
- This FAQ
- User guides
- Training documents
- Video tutorials (planned)

**Getting Started:**
- Setup wizard (on first login)
- Welcome tour
- Sample data
- Best practices guide

**Advanced Topics:**
- eFaktura integration
- Tax obligation management
- KPO processing
- API documentation (future)

---

## Feedback & Features

### Q43: How can I suggest a new feature?
**A:** We welcome feedback!

**How to Suggest:**
1. Use chatbot to describe idea
2. Email support with detailed request
3. Explain use case
4. Share why it would help

**What Happens:**
- Review by product team
- Prioritization
- Development planning
- Implementation
- Notification when released

**Feature Requests:**
- New invoice types
- Additional reports
- Integration requests
- Workflow improvements
- UI enhancements

**Most Requested:**
- Mobile app (native)
- Email invoicing
- Recurring invoices
- Multiple users
- API access

---

### Q44: Can I request a custom feature for my business?
**A:** Custom development options:

**Standard Features:**
- Available to all users
- Included in subscription
- Regular updates

**Custom Features:**
- Requires special development
- May incur additional cost
- Contact for consultation
- Depends on complexity

**Alternatives:**
- Suggest as general feature (free if accepted)
- Use existing features creatively
- Integration via API (when available)
- Export and use external tools

**Best Approach:**
- Explain your need
- We may have existing solution
- Feature might be in development
- Could become standard feature

---

This FAQ covers the most common questions. For anything else, feel free to ask through this chatbot or contact support directly!

---

**Last Updated:** 2025-03-04
**Version:** 1.0
