<?php

namespace App\Console\Commands;

use App\Models\Qms\Asset;
use App\Models\Qms\Capa;
use App\Models\Qms\Incident;
use App\Models\Qms\Notification;
use App\Models\Qms\TrainingRecord;
use App\Services\Qms\Notifier;
use Illuminate\Console\Command;

/**
 * Sends due-date / overdue reminders for incidents, CAPA, training expiry and
 * asset calibration. Schedule daily (routes/console.php) or run manually:
 * php artisan qms:overdue-reminders
 */
class QmsOverdueReminders extends Command
{
    protected $signature = 'qms:overdue-reminders';
    protected $description = 'Notify of overdue incidents, CAPA, training expiry and calibration';

    public function handle(Notifier $notifier): int
    {
        $today = now()->startOfDay();
        $count = 0;

        // Due within the next 7 days OR already overdue — so assignees are warned
        // before the deadline, not only after it passes.
        $horizon = $today->copy()->addDays(7);

        $incidents = Incident::whereNotNull('assigned_to')->whereNotNull('due_date')
            ->where('status', '!=', 'closed')->whereDate('due_date', '<=', $horizon)->get();
        foreach ($incidents as $i) {
            $overdue = $i->due_date->startOfDay()->lt($today);
            $title = ($overdue ? 'Overdue: incident ' : 'Due soon: incident ') . $i->reference;
            if ($this->alreadyNotifiedToday($i->assigned_to, 'qms_incident', $i->id, $title)) {
                continue;
            }
            $notifier->notify($i->assigned_to, 'overdue', $title,
                $i->title . ($overdue ? ' was due ' : ' is due ') . $i->due_date->format('d M Y'),
                'qms_incident', $i->id, true);
            $count++;
        }

        $capas = Capa::whereNotNull('assigned_to')->whereNotNull('due_date')
            ->whereNotIn('status', ['closed', 'cancelled'])->whereDate('due_date', '<=', $horizon)->get();
        foreach ($capas as $c) {
            $overdue = $c->due_date->startOfDay()->lt($today);
            $title = ($overdue ? 'Overdue: CAPA ' : 'Due soon: CAPA ') . $c->reference;
            if ($this->alreadyNotifiedToday($c->assigned_to, 'qms_capa', $c->id, $title)) {
                continue;
            }
            $notifier->notify($c->assigned_to, 'overdue', $title,
                $c->title . ($overdue ? ' was due ' : ' is due ') . $c->due_date->format('d M Y'),
                'qms_capa', $c->id, true);
            $count++;
        }

        // Training expiring or expired (completed records with an expiry within 30 days).
        $training = TrainingRecord::with('training')->where('status', 'completed')
            ->whereNotNull('expiry_date')->whereDate('expiry_date', '<=', $today->copy()->addDays(30))->get();
        foreach ($training as $r) {
            $overdue = $r->expiry_date->isPast();
            $title = ($overdue ? 'Training expired: ' : 'Training expiring: ') . optional($r->training)->title;
            if ($this->alreadyNotifiedToday($r->user_id, 'qms_training', $r->training_id, $title)) {
                continue;
            }
            $notifier->notify($r->user_id, 'overdue', $title,
                'Valid until ' . $r->expiry_date->format('d M Y') . '. Please arrange retraining.',
                'qms_training', $r->training_id, true);
            $count++;
        }

        // Assets due or overdue for calibration.
        $assets = Asset::where('requires_calibration', true)->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<=', $today->copy()->addDays(30))->whereNotNull('created_by')->get();
        foreach ($assets as $a) {
            $overdue = $a->next_due_date->isPast();
            $title = ($overdue ? 'Calibration overdue: ' : 'Calibration due: ') . $a->name . " ({$a->reference})";
            if ($this->alreadyNotifiedToday($a->created_by, null, null, $title)) {
                continue;
            }
            $notifier->notify($a->created_by, 'overdue', $title,
                'Next calibration due ' . $a->next_due_date->format('d M Y') . '.',
                null, null, true);
            $count++;
        }

        $this->info("Sent {$count} reminder(s).");
        return self::SUCCESS;
    }

    /**
     * Idempotency guard: true if an overdue/due-soon reminder for this record was
     * already sent to this user today — so re-running the command the same day
     * does not create duplicate notifications for the same record.
     */
    private function alreadyNotifiedToday(string $userId, ?string $relatedType, ?string $relatedId, string $title): bool
    {
        $q = Notification::where('user_id', $userId)->where('type', 'overdue')
            ->whereDate('created_at', now()->toDateString());
        if ($relatedType && $relatedId) {
            $q->where('related_type', $relatedType)->where('related_id', $relatedId);
        } else {
            $q->where('title', $title);
        }
        return $q->exists();
    }
}
