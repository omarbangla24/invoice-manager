<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'client_profile_id',
    'provider',
    'message_id',
    'from_email',
    'to_email',
    'subject',
    'attachment_count',
    'status',
    'metadata',
])]
class InboundEmail extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(InboundEmailAttachment::class);
    }
}
