<?php

namespace App\Services\Qms;

use App\Models\Qms\Notification;
use Illuminate\Support\Facades\Log;

/**
 * In-app + email notifications. Email is stubbed to the log for local dev;
 * swap the mailer in for production without changing callers.
 */
class Notifier
{
    public function notify(
        string $userId,
        string $type,
        string $title,
        ?string $body = null,
        ?string $relatedType = null,
        ?string $relatedId = null,
        bool $email = false
    ): Notification {
        $n = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'emailed' => $email,
        ]);

        if ($email) {
            // Production: Mail::to(...)->send(...). For now, log so the flow is testable offline.
            Log::info("[QMS email] to={$userId} :: {$title}");
        }

        return $n;
    }
}
