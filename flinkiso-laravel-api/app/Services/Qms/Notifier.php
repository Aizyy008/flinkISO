<?php

namespace App\Services\Qms;

use App\Models\Qms\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * In-app + email notifications.
 * In-app records are always written. Email is sent when $email is true and a
 * mailer is configured; failures are logged and never break the calling flow.
 */
class Notifier
{
    /** Map a related entity to its QMS URL for the "Open in QMS" button. */
    private const LINK_MAP = [
        'qms_incident' => '/incidents/',
        'qms_capa' => '/capa/',
        'qms_risk' => '/risks/',
        'qms_document' => '/documents/',
    ];

    public function notify(
        string $userId,
        string $type,
        string $title,
        ?string $body = null,
        ?string $relatedType = null,
        ?string $relatedId = null,
        bool $email = false
    ): Notification {
        $sent = false;

        if ($email) {
            $link = ($relatedType && $relatedId && isset(self::LINK_MAP[$relatedType]))
                ? rtrim((string) config('app.url'), '/') . self::LINK_MAP[$relatedType] . $relatedId
                : null;
            $sent = $this->sendEmail($userId, $type, $title, $body, $link);
        }

        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'emailed' => $sent,
        ]);
    }

    /** Look up the legacy user's email and send the branded notification. Returns true if actually mailed. */
    private function sendEmail(string $userId, string $type, string $title, ?string $body, ?string $link): bool
    {
        try {
            // Staging/testing override: send everything to one inbox. Empty in production.
            $override = config('flinkiso.notify_override_email');
            $to = $override ?: DB::connection('flinkiso')->table('users')->where('id', $userId)->value('username');

            // FlinkISO usernames are email addresses.
            if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            if (config('mail.default') === 'log') {
                Log::info("[QMS email:log] to={$to} :: {$title}");
                return false; // recorded but not truly delivered
            }

            Mail::send('emails.notification', compact('type', 'title', 'body', 'link'), function ($m) use ($to, $title) {
                $m->to($to)->subject('[FlinkISO QMS] ' . $title);
            });
            return true;
        } catch (\Throwable $e) {
            Log::warning('[QMS email failed] ' . $e->getMessage());
            return false;
        }
    }
}
