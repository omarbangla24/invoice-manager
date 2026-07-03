<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value'])]
class AppSetting extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'encrypted',
        ];
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return self::query()->where('key', $key)->first()?->value ?? $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
