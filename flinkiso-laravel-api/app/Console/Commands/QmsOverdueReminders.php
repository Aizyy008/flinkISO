<?php

namespace App\Console\Commands;

use App\Models\Qms\Capa;
use App\Models\Qms\Incident;
use App\Services\Qms\Notifier;
use Illuminate\Console\Command;

/**
 * Sends due-date / overdue reminders to assignees for open incidents and CAPA.
 * Schedule daily (see routes/console.php) or run manually: php artisan qms:overdue-reminders
 */
class QmsOverdueReminders extends Command
{
    protected $signature = 'qms:overdue-reminders';
    protected $description = 'Notify assignees of overdue incidents and CAPA';

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

        $this->info("Sent {$count} overdue reminder(s).");
        return self::SUCCESS;
    }
}
