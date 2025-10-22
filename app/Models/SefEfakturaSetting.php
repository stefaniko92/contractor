<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SefEfakturaSetting extends Model
{
    /** @use HasFactory<\Database\Factories\SefEfakturaSettingFactory> */
    use HasFactory;

    protected $table = 'sef_efaktura_settings';

    protected $fillable = [
        'user_id',
        'api_key',
        'is_enabled',
        'default_vat_exemption',
        'default_vat_category',
        'webhook_url',
        'last_webhook_test',
        'integration_data',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'last_webhook_test' => 'datetime',
            'integration_data' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate the webhook URL for this integration
     */
    public function generateWebhookUrl(): string
    {
        return route('webhooks.sef-efaktura', [
            'user_id' => $this->user_id,
            'token' => hash('sha256', $this->user_id.$this->created_at->timestamp),
        ]);
    }
}
