<?php

namespace Database\Seeders;

use App\Models\Qms\Capa;
use App\Models\Qms\Evidence;
use App\Models\Qms\Incident;
use App\Models\Qms\Risk;
use App\Models\Qms\Workflow;
use App\Services\Qms\AuditTrailService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sample Milestone 1.2 workflow for acceptance testing (Incident → CAPA → closure,
 * Risk, and a live Workflow rule). Idempotent.
 */
class QmsCoreDemoSeeder extends Seeder
{
    public function run(): void
    {
        $audit = app(AuditTrailService::class);
        $u = DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->first();
        if (!$u) {
            $this->command->warn('No FlinkISO user found — skipping.');
            return;
        }
        $year = date('Y');

        // 1) A live workflow rule: critical incident → auto CAPA + notify.
        if (!Workflow::where('name', 'DEMO critical incident auto-CAPA')->exists()) {
            Workflow::create([
                'name' => 'DEMO critical incident auto-CAPA',
                'trigger_event' => 'incident.created',
                'conditions' => [['field' => 'severity', 'op' => '=', 'value' => 'critical']],
                'actions' => [
                    ['type' => 'create_capa', 'params' => ['type' => 'corrective', 'title' => 'Auto CAPA from critical incident']],
                    ['type' => 'notify', 'params' => ['title' => 'Critical incident logged', 'email' => false]],
                ],
                'active' => true,
                'created_by' => $u->id,
            ]);
            $this->command->info('Seeded workflow rule: critical incident → auto CAPA + notify.');
        }

        // 2) A sample incident with evidence, and a CAPA taken to closure.
        if (!Incident::where('reference', 'like', 'INC ' . $year . ' 9001')->exists()) {
            $inc = Incident::create([
                'reference' => "INC $year 9001", 'type' => 'deviation', 'title' => 'DEMO Cold storage over temperature',
                'description' => 'Cold room exceeded 8°C for 2 hours.', 'severity' => 'high', 'source' => 'Temperature sensor',
                'root_cause' => 'Compressor fault', 'containment_action' => 'Product quarantined',
                'status' => 'capa_raised', 'assigned_to' => $u->id, 'detected_by' => $u->id,
                'detected_date' => now()->subDays(3)->toDateString(), 'created_by' => $u->id,
            ]);
            $audit->record('qms_incident', $inc->id, 'create', ['user_id' => $u->id, 'username' => $u->username, 'changes' => ['new' => ['reference' => $inc->reference]]]);
            Evidence::create([
                'related_type' => 'qms_incident', 'related_id' => $inc->id, 'evidence_type' => 'record',
                'title' => 'Temperature log extract', 'json_data' => ['note' => 'Log shows 8–11°C from 02:00–04:00.'],
                'record_date' => now()->subDays(3)->toDateString(), 'created_by' => $u->id,
            ]);

            $capa = Capa::create([
                'reference' => "CAPA $year 9001", 'incident_id' => $inc->id, 'type' => 'corrective',
                'title' => 'Repair compressor and add high-temp alarm', 'root_cause' => 'Compressor fault',
                'action_plan' => 'Replace compressor; install independent high-temperature alarm.',
                'priority' => 'high', 'status' => 'closed', 'assigned_to' => $u->id, 'due_date' => now()->addDays(7)->toDateString(),
                'effectiveness_notes' => 'Alarm tested; no recurrence in 30 days.', 'effectiveness_verified' => true,
                'verified_by' => $u->id, 'closed_at' => now(), 'created_by' => $u->id,
            ]);
            $audit->record('qms_capa', $capa->id, 'create', ['user_id' => $u->id, 'username' => $u->username, 'changes' => ['new' => ['reference' => $capa->reference], 'incident_id' => $inc->id]]);
            $this->command->info('Seeded incident INC ' . $year . ' 9001 → CAPA (closed with effectiveness check).');
        }

        // 3) A couple of risks with calculated scores.
        if (!Risk::where('reference', 'like', 'RISK ' . $year . ' 9001')->exists()) {
            foreach ([
                ['RISK ' . $year . ' 9001', 'DEMO Microbial contamination', 4, 5, 3, 'food_safety'],
                ['RISK ' . $year . ' 9002', 'DEMO Data breach of QMS records', 3, 4, 2, 'info_security'],
            ] as [$ref, $title, $l, $s, $d, $hz]) {
                $risk = new Risk([
                    'reference' => $ref, 'title' => $title, 'hazard_type' => $hz, 'context' => 'process',
                    'likelihood' => $l, 'severity' => $s, 'detection' => $d, 'status' => 'open',
                    'owner_id' => $u->id, 'created_by' => $u->id,
                ]);
                $risk->recalculate();   // score = L×S×D, level derived
                $risk->save();
            }
            $this->command->info('Seeded 2 risks with calculated scores.');
        }
    }
}
