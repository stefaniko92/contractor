<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'external_id',
        'name',
        'code',
        'swift',
        'hide',
        'agencies_count',
    ];

    protected $casts = [
        'hide' => 'boolean',
        'agencies_count' => 'integer',
    ];

    public static function getActiveBanks(): array
    {
        return self::where('hide', false)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function getBankWithSwift(int $bankId): ?array
    {
        $bank = self::find($bankId);

        if (!$bank) {
            return null;
        }

        return [
            'name' => $bank->name,
            'swift' => $bank->swift,
            'code' => $bank->code,
        ];
    }
}
