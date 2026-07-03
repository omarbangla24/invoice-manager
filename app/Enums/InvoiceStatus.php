<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Pending = 'pending';
    case Done = 'done';
    case Declined = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Done => 'Done / Counted',
            self::Declined => 'Declined / Returned',
        };
    }
}
