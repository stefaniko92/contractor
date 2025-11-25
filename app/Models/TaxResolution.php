<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxResolution extends Model
{
    protected $fillable = [
        'user_id', 'file_path', 'file_name', 'file_size', 'mime_type',
        'year', 'type', 'resolution_number', 'status',
        'extraction_data', 'error_message', 'processed_at',
    ];

    protected $casts = [
        'extraction_data' => 'array',
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function obligations(): HasMany
    {
        return $this->hasMany(TaxObligation::class);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed', 'processed_at' => now()]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update(['status' => 'failed', 'error_message' => $error]);
    }
}
