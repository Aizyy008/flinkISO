<?php

namespace Database\Seeders;

use App\Models\Qms\Kpi;
use App\Models\Qms\Workflow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sample KPIs with periodic results for the calculated dashboard.
 * Idempotent PER KPI (keyed on name) so it seeds the demo set even when other
 * KPIs already exist, and never duplicates on re-run.
 */
class KpiDemoSeeder extends Seeder
{
    public function run(): void
    {
        $u = DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->value('id');
        $year = date('Y');

        // name, area, standard, clause, unit, target, warn, crit, direction, dept, process, freq, dataSource, calc, [monthly values]
        $defs = [
            ['On-Time Delivery', 'quality', 'ISO 9001', '8.5', '%', 95, 90, 85, 'higher_better', 'Logistics', 'Order fulfilment', 'monthly', 'ERP export', 'on-time deliveries / total * 100', [96, 94, 92, 88]],
            ['Customer Complaints', 'quality', 'ISO 9001', '9.1.2', 'count', 2, 4, 6, 'lower_better', 'Quality', 'Customer feedback', 'monthly', 'CRM', 'count of complaints in period', [1, 3, 5, 7]],
            ['Energy Use per Unit', 'environment', 'ISO 14001', '6.1', 'kWh', 10, 12, 15, 'lower_better', 'Facilities', 'Production', 'monthly', 'Meter readings', 'total kWh / units produced', [9, 10, 11, 13]],
            ['Lost-Time Injuries', 'safety', 'ISO 45001', '6.1.2', 'count', 0, 1, 2, 'lower_better', 'HSE', 'All operations', 'monthly', 'Incident log', 'count of LTIs in period', [0, 0, 1, 0]],
            ['CCP Compliance', 'food_safety', 'ISO 22000', '8.5.4', '%', 100, 98, 95, 'higher_better', 'Production', 'Pasteurization', 'monthly', 'HACCP logs', 'in-limit readings / total * 100', [100, 99, 97, 100]],
        ];

        $created = 0;
        foreach ($defs as [$name, $area, $standard, $clause, $unit, $target, $warn, $crit, $dir, $dept, $process, $freq, $source, $calc, $vals]) {
            if (Kpi::where('name', $name)->exists()) {
                continue;   // already seeded — leave it (and its results) alone
            }
            $kpi = Kpi::create([
                'reference' => "KPI $year " . strtoupper(substr(md5($name), 0, 5)),
                'name' => $name,
                'description' => $name . ' — demo KPI for acceptance testing.',
                'area' => $area, 'standard' => $standard, 'clause' => $clause,
                'unit' => $unit, 'target_value' => $target, 'warning_threshold' => $warn, 'critical_threshold' => $crit,
                'direction' => $dir, 'aggregation' => 'monthly', 'frequency' => $freq, 'data_source' => $source,
                'calculation_method' => $calc, 'related_department' => $dept, 'related_process' => $process,
                'related_site' => 'Plant A', 'owner_id' => $u, 'status' => 'active', 'created_by' => $u,
            ]);
            foreach ($vals as $m => $v) {
                $kpi->results()->create([
                    'period_label' => sprintf('%s-%02d', $year, $m + 1),
                    'period_date' => sprintf('%s-%02d-28', $year, $m + 1),
                    'value' => $v, 'recorded_by' => $u,
                ]);
            }
            $created++;
        }

        // A live workflow rule so a KPI threshold breach is visibly actioned
        // (notify the KPI owner + raise a CAPA) — this is the "threshold workflow"
        // the acceptance guide's KPI step describes. Idempotent.
        Workflow::firstOrCreate(
            ['trigger_event' => 'kpi.threshold_breached', 'name' => 'KPI breach → notify + CAPA'],
            [
                'conditions' => [],
                'actions' => [
                    ['type' => 'notify', 'params' => ['title' => 'KPI threshold breached', 'body' => 'A KPI has breached its warning/critical threshold — please review.', 'email' => true]],
                    ['type' => 'create_capa', 'params' => ['type' => 'corrective', 'title' => 'CAPA from KPI threshold breach']],
                ],
                'active' => true,
                'created_by' => $u,
            ]
        );

        $this->command->info(($created ? "Seeded {$created} demo KPI(s) with monthly results." : 'Demo KPIs already present.') . ' KPI breach workflow rule ensured.');
    }
}
