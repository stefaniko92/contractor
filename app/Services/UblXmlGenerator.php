<?php

namespace App\Services;

use App\Models\Invoice;
use DOMDocument;
use DOMElement;

/**
 * UBL 2.1 XML Generator for Serbian eFaktura System
 *
 * This service generates UBL (Universal Business Language) XML documents
 * compliant with the Serbian eFaktura system requirements.
 */
class UblXmlGenerator
{
    private DOMDocument $doc;

    private string $namespaceURI = 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2';

    private array $namespaces = [
        'cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
        'cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
        'ccts' => 'urn:un:unece:uncefact:documentation:2',
        'qdt' => 'urn:oasis:names:specification:ubl:schema:xsd:QualifiedDataTypes-2',
        'udt' => 'urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2',
    ];

    public function __construct()
    {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
    }

    /**
     * Generate UBL XML for an invoice
     */
    public function generate(Invoice $invoice): string
    {
        // Load relationships
        $invoice->load(['user.userCompany', 'client', 'items', 'bankAccount']);

        // Create root Invoice element
        $root = $this->createRootElement();

        // Add UBL Version
        $this->addElement($root, 'cbc:UBLVersionID', '2.1');

        // Add Customization ID (Serbian eFaktura profile)
        $this->addElement($root, 'cbc:CustomizationID', 'urn:cen.eu:en16931:2017#compliant#urn:mfin.gov.rs:sfkv:2.0');

        // Add Profile ID
        $this->addElement($root, 'cbc:ProfileID', 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0');

        // Invoice Number and Type
        $this->addElement($root, 'cbc:ID', $invoice->invoice_number);
        $this->addElement($root, 'cbc:IssueDate', $invoice->issue_date->format('Y-m-d'));

        if ($invoice->due_date) {
            $this->addElement($root, 'cbc:DueDate', $invoice->due_date->format('Y-m-d'));
        }

        // Invoice Type Code
        $invoiceTypeCode = $this->getInvoiceTypeCode($invoice);
        $this->addElement($root, 'cbc:InvoiceTypeCode', $invoiceTypeCode);

        // Note/Description
        if ($invoice->description) {
            $this->addElement($root, 'cbc:Note', $invoice->description);
        }

        // Document Currency Code
        $this->addElement($root, 'cbc:DocumentCurrencyCode', $invoice->currency ?? 'RSD');

        // Accounting Cost (optional)
        if ($invoice->trading_place) {
            $this->addElement($root, 'cbc:AccountingCost', $invoice->trading_place);
        }

        // Add Supplier Party (Your Company)
        $this->addSupplierParty($root, $invoice);

        // Add Customer Party (Client)
        $this->addCustomerParty($root, $invoice);

        // Add Payment Means
        $this->addPaymentMeans($root, $invoice);

        // Add Tax Total
        $this->addTaxTotal($root, $invoice);

        // Add Legal Monetary Total
        $this->addLegalMonetaryTotal($root, $invoice);

        // Add Invoice Lines
        $this->addInvoiceLines($root, $invoice);

        return $this->doc->saveXML();
    }

    /**
     * Create root Invoice element with namespaces
     */
    private function createRootElement(): DOMElement
    {
        $root = $this->doc->createElementNS($this->namespaceURI, 'Invoice');
        $this->doc->appendChild($root);

        // Add namespace declarations
        foreach ($this->namespaces as $prefix => $uri) {
            $root->setAttributeNS('http://www.w3.org/2000/xmlns/', "xmlns:$prefix", $uri);
        }

        return $root;
    }

    /**
     * Add Supplier Party (Your Company)
     */
    private function addSupplierParty(DOMElement $parent, Invoice $invoice): void
    {
        $userCompany = $invoice->user->userCompany;

        $accountingSupplierParty = $this->createElement($parent, 'cac:AccountingSupplierParty');
        $party = $this->createElement($accountingSupplierParty, 'cac:Party');

        // Endpoint ID (required by Serbian eFaktura)
        $endpointID = $this->createElement($party, 'cbc:EndpointID');
        $endpointID->setAttribute('schemeID', '9948');
        $endpointID->nodeValue = htmlspecialchars($userCompany->company_tax_id ?? '', ENT_XML1, 'UTF-8');

        // Party Name
        $partyName = $this->createElement($party, 'cac:PartyName');
        $this->addElement($partyName, 'cbc:Name', $userCompany->company_full_name ?? $userCompany->company_name);

        // Postal Address
        $postalAddress = $this->createElement($party, 'cac:PostalAddress');
        $this->addElement($postalAddress, 'cbc:StreetName', $userCompany->company_address ?? '');
        if ($userCompany->company_address_number) {
            $this->addElement($postalAddress, 'cbc:BuildingNumber', $userCompany->company_address_number);
        }
        $this->addElement($postalAddress, 'cbc:CityName', $userCompany->company_city ?? '');
        $this->addElement($postalAddress, 'cbc:PostalZone', $userCompany->company_postal_code ?? '');

        $country = $this->createElement($postalAddress, 'cac:Country');
        $this->addElement($country, 'cbc:IdentificationCode', 'RS');

        // Party Tax Scheme
        $partyTaxScheme = $this->createElement($party, 'cac:PartyTaxScheme');
        $companyTaxId = $this->createElement($partyTaxScheme, 'cbc:CompanyID');
        $companyTaxId->setAttribute('schemeID', '9948'); // Serbian Tax ID scheme
        // Add RS prefix if not already present
        $taxId = $userCompany->company_tax_id ?? '';
        if ($taxId && ! str_starts_with($taxId, 'RS')) {
            $taxId = 'RS'.$taxId;
        }
        $companyTaxId->nodeValue = htmlspecialchars($taxId, ENT_XML1, 'UTF-8');
        $taxScheme = $this->createElement($partyTaxScheme, 'cac:TaxScheme');
        $this->addElement($taxScheme, 'cbc:ID', 'VAT');

        // Party Legal Entity
        $partyLegalEntity = $this->createElement($party, 'cac:PartyLegalEntity');
        $this->addElement($partyLegalEntity, 'cbc:RegistrationName', $userCompany->company_full_name ?? $userCompany->company_name);
        if ($userCompany->company_registry_number) {
            $companyRegId = $this->createElement($partyLegalEntity, 'cbc:CompanyID');
            $companyRegId->setAttribute('schemeID', '9948'); // Serbian registration number scheme
            $companyRegId->nodeValue = htmlspecialchars($userCompany->company_registry_number, ENT_XML1, 'UTF-8');
        }

        // Contact
        if ($userCompany->company_email || $userCompany->company_phone) {
            $contact = $this->createElement($party, 'cac:Contact');
            if ($userCompany->company_phone) {
                $this->addElement($contact, 'cbc:Telephone', $userCompany->company_phone);
            }
            if ($userCompany->company_email) {
                $this->addElement($contact, 'cbc:ElectronicMail', $userCompany->company_email);
            }
        }
    }

    /**
     * Add Customer Party (Client)
     */
    private function addCustomerParty(DOMElement $parent, Invoice $invoice): void
    {
        $client = $invoice->client;

        $accountingCustomerParty = $this->createElement($parent, 'cac:AccountingCustomerParty');
        $party = $this->createElement($accountingCustomerParty, 'cac:Party');

        // Endpoint ID (only include for customer if they are registered in eFaktura system)
        // For customers not in eFaktura, we skip the EndpointID
        // Uncomment below if customer is registered in eFaktura:
        // if ($client->tax_id) {
        //     $endpointID = $this->createElement($party, 'cbc:EndpointID');
        //     $endpointID->setAttribute('schemeID', '9948');
        //     $endpointID->nodeValue = htmlspecialchars($client->tax_id, ENT_XML1, 'UTF-8');
        // }

        // Party Name
        $partyName = $this->createElement($party, 'cac:PartyName');
        $this->addElement($partyName, 'cbc:Name', $client->company_name);

        // Postal Address
        if ($client->address || $client->city) {
            $postalAddress = $this->createElement($party, 'cac:PostalAddress');
            if ($client->address) {
                $this->addElement($postalAddress, 'cbc:StreetName', $client->address);
            }
            if ($client->city) {
                $this->addElement($postalAddress, 'cbc:CityName', $client->city);
            }

            $country = $this->createElement($postalAddress, 'cac:Country');
            $countryCode = $client->country ?? ($client->is_domestic ? 'RS' : 'XX');
            $this->addElement($country, 'cbc:IdentificationCode', $countryCode);
        }

        // Party Tax Scheme (if client has tax ID)
        if ($client->tax_id) {
            $partyTaxScheme = $this->createElement($party, 'cac:PartyTaxScheme');
            $clientTaxId = $this->createElement($partyTaxScheme, 'cbc:CompanyID');
            $clientTaxId->setAttribute('schemeID', '9948'); // Serbian Tax ID scheme
            // Add RS prefix if not already present
            $taxId = $client->tax_id;
            if ($taxId && ! str_starts_with($taxId, 'RS')) {
                $taxId = 'RS'.$taxId;
            }
            $clientTaxId->nodeValue = htmlspecialchars($taxId, ENT_XML1, 'UTF-8');
            $taxScheme = $this->createElement($partyTaxScheme, 'cac:TaxScheme');
            $this->addElement($taxScheme, 'cbc:ID', 'VAT');
        }

        // Party Legal Entity
        $partyLegalEntity = $this->createElement($party, 'cac:PartyLegalEntity');
        $this->addElement($partyLegalEntity, 'cbc:RegistrationName', $client->company_name);
        if ($client->registration_number) {
            $clientRegId = $this->createElement($partyLegalEntity, 'cbc:CompanyID');
            $clientRegId->setAttribute('schemeID', '9948'); // Serbian registration number scheme
            $clientRegId->nodeValue = htmlspecialchars($client->registration_number, ENT_XML1, 'UTF-8');
        }

        // Contact
        if ($client->email || $client->phone) {
            $contact = $this->createElement($party, 'cac:Contact');
            if ($client->phone) {
                $this->addElement($contact, 'cbc:Telephone', $client->phone);
            }
            if ($client->email) {
                $this->addElement($contact, 'cbc:ElectronicMail', $client->email);
            }
        }
    }

    /**
     * Add Payment Means
     */
    private function addPaymentMeans(DOMElement $parent, Invoice $invoice): void
    {
        $paymentMeans = $this->createElement($parent, 'cac:PaymentMeans');

        // Payment means code (30 = Credit transfer, 31 = Debit transfer)
        $this->addElement($paymentMeans, 'cbc:PaymentMeansCode', '30');

        // Payment ID
        if ($invoice->invoice_number) {
            $this->addElement($paymentMeans, 'cbc:PaymentID', $invoice->invoice_number);
        }

        // Bank account details
        if ($invoice->bankAccount) {
            $payeeFinancialAccount = $this->createElement($paymentMeans, 'cac:PayeeFinancialAccount');
            $this->addElement($payeeFinancialAccount, 'cbc:ID', $invoice->bankAccount->account_number);
            $this->addElement($payeeFinancialAccount, 'cbc:Name', $invoice->user->userCompany->company_name);

            if ($invoice->bankAccount->bank_name) {
                $financialInstitutionBranch = $this->createElement($payeeFinancialAccount, 'cac:FinancialInstitutionBranch');
                $this->addElement($financialInstitutionBranch, 'cbc:ID', $invoice->bankAccount->swift_code ?? '');
                $this->addElement($financialInstitutionBranch, 'cbc:Name', $invoice->bankAccount->bank_name);
            }
        }
    }

    /**
     * Add Tax Total
     */
    private function addTaxTotal(DOMElement $parent, Invoice $invoice): void
    {
        $taxTotal = $this->createElement($parent, 'cac:TaxTotal');

        // For Serbian flat-tax entrepreneurs (paušalci), there's no VAT
        // Tax amount is 0
        $taxAmount = $this->createElement($taxTotal, 'cbc:TaxAmount');
        $taxAmount->setAttribute('currencyID', $invoice->currency ?? 'RSD');
        $taxAmount->nodeValue = '0.00';

        // Tax Subtotal
        $taxSubtotal = $this->createElement($taxTotal, 'cac:TaxSubtotal');

        $taxableAmount = $this->createElement($taxSubtotal, 'cbc:TaxableAmount');
        $taxableAmount->setAttribute('currencyID', $invoice->currency ?? 'RSD');
        $taxableAmount->nodeValue = number_format($invoice->amount, 2, '.', '');

        $taxAmountSub = $this->createElement($taxSubtotal, 'cbc:TaxAmount');
        $taxAmountSub->setAttribute('currencyID', $invoice->currency ?? 'RSD');
        $taxAmountSub->nodeValue = '0.00';

        // Tax Category
        $taxCategory = $this->createElement($taxSubtotal, 'cac:TaxCategory');
        $this->addElement($taxCategory, 'cbc:ID', 'E'); // E = Exempt from tax
        $this->addElement($taxCategory, 'cbc:Percent', '0');

        // Tax exemption reason code for Serbian flat-tax entrepreneurs
        $this->addElement($taxCategory, 'cbc:TaxExemptionReasonCode', 'vatex-eu-79c');
        $this->addElement($taxCategory, 'cbc:TaxExemptionReason', 'Oslobođenje PDV-a - Paušalno oporezivanje');

        $taxScheme = $this->createElement($taxCategory, 'cac:TaxScheme');
        $this->addElement($taxScheme, 'cbc:ID', 'VAT');
    }

    /**
     * Add Legal Monetary Total
     */
    private function addLegalMonetaryTotal(DOMElement $parent, Invoice $invoice): void
    {
        $legalMonetaryTotal = $this->createElement($parent, 'cac:LegalMonetaryTotal');

        $amount = (float) $invoice->amount;
        $currency = $invoice->currency ?? 'RSD';

        // Line Extension Amount (sum of line amounts before tax)
        $lineExtensionAmount = $this->createElement($legalMonetaryTotal, 'cbc:LineExtensionAmount');
        $lineExtensionAmount->setAttribute('currencyID', $currency);
        $lineExtensionAmount->nodeValue = number_format($amount, 2, '.', '');

        // Tax Exclusive Amount
        $taxExclusiveAmount = $this->createElement($legalMonetaryTotal, 'cbc:TaxExclusiveAmount');
        $taxExclusiveAmount->setAttribute('currencyID', $currency);
        $taxExclusiveAmount->nodeValue = number_format($amount, 2, '.', '');

        // Tax Inclusive Amount
        $taxInclusiveAmount = $this->createElement($legalMonetaryTotal, 'cbc:TaxInclusiveAmount');
        $taxInclusiveAmount->setAttribute('currencyID', $currency);
        $taxInclusiveAmount->nodeValue = number_format($amount, 2, '.', '');

        // Payable Amount
        $payableAmount = $this->createElement($legalMonetaryTotal, 'cbc:PayableAmount');
        $payableAmount->setAttribute('currencyID', $currency);
        $payableAmount->nodeValue = number_format($amount, 2, '.', '');
    }

    /**
     * Add Invoice Lines
     */
    private function addInvoiceLines(DOMElement $parent, Invoice $invoice): void
    {
        $lineNumber = 1;

        foreach ($invoice->items as $item) {
            $invoiceLine = $this->createElement($parent, 'cac:InvoiceLine');

            // Line ID
            $this->addElement($invoiceLine, 'cbc:ID', (string) $lineNumber);

            // Quantity
            $quantity = $this->createElement($invoiceLine, 'cbc:InvoicedQuantity');
            $quantity->setAttribute('unitCode', $this->getUnitCode($item->unit));
            $quantity->nodeValue = number_format($item->quantity, 2, '.', '');

            // Line Extension Amount
            $lineExtensionAmount = $this->createElement($invoiceLine, 'cbc:LineExtensionAmount');
            $lineExtensionAmount->setAttribute('currencyID', $invoice->currency ?? 'RSD');
            $lineExtensionAmount->nodeValue = number_format($item->amount, 2, '.', '');

            // Item
            $itemElement = $this->createElement($invoiceLine, 'cac:Item');
            $this->addElement($itemElement, 'cbc:Description', $item->description ?? $item->title);
            $this->addElement($itemElement, 'cbc:Name', $item->title);

            // Classified Tax Category
            $classifiedTaxCategory = $this->createElement($itemElement, 'cac:ClassifiedTaxCategory');
            $this->addElement($classifiedTaxCategory, 'cbc:ID', 'E');
            $this->addElement($classifiedTaxCategory, 'cbc:Percent', '0');

            $taxScheme = $this->createElement($classifiedTaxCategory, 'cac:TaxScheme');
            $this->addElement($taxScheme, 'cbc:ID', 'VAT');

            // Price
            $price = $this->createElement($invoiceLine, 'cac:Price');
            $priceAmount = $this->createElement($price, 'cbc:PriceAmount');
            $priceAmount->setAttribute('currencyID', $invoice->currency ?? 'RSD');
            $priceAmount->nodeValue = number_format($item->unit_price, 2, '.', '');

            $lineNumber++;
        }
    }

    /**
     * Get invoice type code
     */
    private function getInvoiceTypeCode(Invoice $invoice): string
    {
        if ($invoice->is_storno) {
            return '384'; // Corrected invoice (credit note for storno)
        }

        return match ($invoice->invoice_document_type) {
            'profaktura' => '325', // Proforma invoice
            'avansna_faktura' => '386', // Prepayment invoice
            default => '380', // Commercial invoice
        };
    }

    /**
     * Get unit code from unit description
     */
    private function getUnitCode(?string $unit): string
    {
        if (! $unit) {
            return 'C62'; // One (piece) - default unit
        }

        return match (strtolower($unit)) {
            'sat', 'h', 'hour', 'hours' => 'HUR',
            'dan', 'day', 'days' => 'DAY',
            'mesec', 'month', 'months' => 'MON',
            'komad', 'kom', 'piece', 'pieces', 'pcs' => 'H87',
            'kg', 'kilogram' => 'KGM',
            'litar', 'l', 'liter' => 'LTR',
            'm', 'metar', 'meter' => 'MTR',
            'm2', 'kvadratni metar' => 'MTK',
            'm3', 'kubni metar' => 'MTQ',
            default => 'C62', // One (piece) - default unit
        };
    }

    /**
     * Helper method to create element with namespace
     */
    private function createElement(DOMElement $parent, string $qualifiedName): DOMElement
    {
        [$prefix, $localName] = explode(':', $qualifiedName);
        $namespace = $this->namespaces[$prefix] ?? $this->namespaceURI;

        $element = $this->doc->createElementNS($namespace, $qualifiedName);
        $parent->appendChild($element);

        return $element;
    }

    /**
     * Helper method to add element with text content
     */
    private function addElement(DOMElement $parent, string $qualifiedName, string $value): DOMElement
    {
        $element = $this->createElement($parent, $qualifiedName);
        $element->nodeValue = htmlspecialchars($value, ENT_XML1, 'UTF-8');

        return $element;
    }
}
