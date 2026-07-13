<?php

namespace App\Console\Commands;

use App\Models\Qms\Asset;
use App\Models\Qms\Capa;
use App\Models\Qms\Incident;
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

        $incidents = Incident::whereNotNull('assigned_to')->whereNotNull('due_date')
            ->where('status', '!=', 'closed')->whereDate('due_date', '<', $today)->get();
        foreach ($incidents as $i) {
            $notifier->notify($i->assigned_to, 'overdue',
                "Overdue: incident {$i->reference}",
                $i->title . ' was due ' . $i->due_date->format('d M Y'),
                'qms_incident', $i->id, true);
            $count++;
        }

        $capas = Capa::whereNotNull('assigned_to')->whereNotNull('due_date')
            ->whereNotIn('status', ['closed', 'cancelled'])->whereDate('due_date', '<', $today)->get();
        foreach ($capas as $c) {
            $notifier->notify($c->assigned_to, 'overdue',
                "Overdue: CAPA {$c->reference}",
                $c->title . ' was due ' . $c->due_date->format('d M Y'),
                'qms_capa', $c->id, true);
            $count++;
        }

        // Training expiring or expired (completed records with an expiry within 30 days).
        $training = TrainingRecord::with('training')->where('status', 'completed')
            ->whereNotNull('expiry_date')->whereDate('expiry_date', '<=', $today->copy()->addDays(30))->get();
        foreach ($training as $r) {
            $overdue = $r->expiry_date->isPast();
            $notifier->notify($r->user_id, 'overdue',
                ($overdue ? 'Training expired: ' : 'Training expiring: ') . optional($r->training)->title,
                'Valid until ' . $r->expiry_date->format('d M Y') . '. Please arrange retraining.',
                'qms_training', $r->training_id, true);
            $count++;
        }

        // Assets due or overdue for calibration.
        $assets = Asset::where('requires_calibration', true)->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<=', $today->copy()->addDays(30))->whereNotNull('created_by')->get();
        foreach ($assets as $a) {
            $overdue = $a->next_due_date->isPast();
            $notifier->notify($a->created_by, 'overdue',
                ($overdue ? 'Calibration overdue: ' : 'Calibration due: ') . $a->name . " ({$a->reference})",
                'Next calibration due ' . $a->next_due_date->format('d M Y') . '.',
                null, null, true);
            $count++;
        }

        $this->info("Sent {$count} reminder(s).");
        return self::SUCCESS;
    }
}
