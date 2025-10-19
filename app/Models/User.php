<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'citizenship',
        'language',
        'password',
        'company_name',
        'tax_id',
        'address',
        'phone',
        'default_currency',
        'logo_path',
        'swift_code',
        'iban',
        'is_grandfathered',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_grandfathered' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Check if user has an active subscription or is grandfathered
     */
    public function hasActiveSubscriptionOrGrandfathered(): bool
    {
        return $this->is_grandfathered || $this->subscribed('default');
    }

    /**
     * Check if user is on free plan
     */
    public function isOnFreePlan(): bool
    {
        return ! $this->is_grandfathered && ! $this->subscribed('default');
    }

    /**
     * Get monthly invoice limit based on subscription
     */
    public function getMonthlyInvoiceLimit(): int
    {
        if ($this->is_grandfathered) {
            return PHP_INT_MAX; // Unlimited for grandfathered users
        }

        if ($this->subscribed('default')) {
            return PHP_INT_MAX; // Unlimited for paid subscribers
        }

        return 3; // Free plan limit
    }

    /**
     * Get invoices count for current month
     */
    public function getMonthlyInvoiceCount(): int
    {
        return $this->invoices()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('invoice_document_type', 'faktura')
            ->count();
    }

    /**
     * Check if user can create more invoices this month
     */
    public function canCreateInvoice(): bool
    {
        return $this->getMonthlyInvoiceCount() < $this->getMonthlyInvoiceLimit();
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function obligations(): HasMany
    {
        return $this->hasMany(Obligation::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function userCompany(): HasOne
    {
        return $this->hasOne(UserCompany::class);
    }

    public function companyOwner()
    {
        return $this->hasOneThrough(
            CompanyOwner::class,
            UserCompany::class,
            'user_id', // Foreign key on UserCompany table
            'user_company_id', // Foreign key on CompanyOwner table
            'id', // Local key on User table
            'id' // Local key on UserCompany table
        );
    }
}
