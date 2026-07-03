<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'client_profile_id',
    'uploaded_by',
    'source',
    'status',
    'title',
    'description',
    'expense_date',
    'amount',
    'currency',
    'original_filename',
    'stored_path',
    'compressed_path',
    'original_size',
    'compressed_size',
    'mime_type',
    'storage_disk',
    'optimization_status',
    'optimization_notes',
    'counted_at',
    'declined_at',
])]
class Invoice extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'counted_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(InvoiceComment::class);
    }
}
