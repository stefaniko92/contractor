<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyOwner extends Model
{
    protected $fillable = [
        'user_company_id',
        'first_name',
        'last_name',
        'parent_name',
        'nationality',
        'personal_id_number',
        'education_level',
        'gender',
        'city',
        'municipality',
        'address',
        'address_number',
        'email',
    ];

    public function userCompany(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class);
    }
}
