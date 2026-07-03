<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'inbound_email_id',
    'invoice_id',
    'status',
    'original_filename',
    'stored_path',
    'size',
    'mime_type',
    'storage_disk',
    'transferred_at',
])]
class InboundEmailAttachment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'transferred_at' => 'datetime',
        ];
    }

    public function inboundEmail(): BelongsTo
    {
        return $this->belongsTo(InboundEmail::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
