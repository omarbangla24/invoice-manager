<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'type_of_service',
    'category',
    'description',
    'qty',
    'fees',
    'is_active',
    'attachment_path',
    'attachment_filename',
    'attachment_mime',
])]
class Job extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected function casts(): array
    {
        return [
            'fees' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function jobRequestItems(): HasMany
    {
        return $this->hasMany(JobRequestItem::class);
    }
}
