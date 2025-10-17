<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserCompany extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'company_full_name',
        'company_tax_id',
        'company_registry_number',
        'company_activity_code',
        'company_activity_desc',
        'company_registration_date',
        'company_city',
        'company_postal_code',
        'company_status',
        'company_municipality',
        'company_address',
        'company_address_number',
        'company_phone',
        'company_email',
        'show_email_on_invoice',
        'company_logo_path',
        'invoice_note_domestic',
        'invoice_note_foreign',
    ];

    protected $casts = [
        'company_registration_date' => 'date',
        'show_email_on_invoice' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function companyOwner(): HasOne
    {
        return $this->hasOne(CompanyOwner::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }
}
