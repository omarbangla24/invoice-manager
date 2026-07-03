<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

class Notifier
{
    public function notify(User $user, string $title, ?string $body = null, ?string $url = null): void
    {
        AppNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'url' => $url,
        ]);
    }
}
