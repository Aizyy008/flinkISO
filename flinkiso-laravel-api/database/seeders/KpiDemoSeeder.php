<?php

namespace Database\Seeders;

use App\Models\Qms\Kpi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Sample KPIs with periodic results for the calculated dashboard. Idempotent. */
class KpiDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Kpi::where('reference', 'like', 'KPI ' . date('Y') . ' %')->exists()) {
            $this->command->info('KPI demo already exists, skipping.');
            return;
        }
        $u = DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->value('id');
        $year = date('Y');
        $defs = [
            // name, area, unit, target, warn, crit, direction, [monthly values]
            ['On-Time Delivery', 'quality', '%', 95, 90, 85, 'higher_better', [96, 94, 92, 88]],
            ['Customer Complaints', 'quality', 'count', 2, 4, 6, 'lower_better', [1, 3, 5, 7]],
            ['Energy Use per Unit', 'environment', 'kWh', 10, 12, 15, 'lower_better', [9, 10, 11, 13]],
            ['Lost-Time Injuries', 'safety', 'count', 0, 1, 2, 'lower_better', [0, 0, 1, 0]],
            ['CCP Compliance', 'food_safety', '%', 100, 98, 95, 'higher_better', [100, 99, 97, 100]],
        ];
        $i = 1;
        foreach ($defs as [$name, $area, $unit, $target, $warn, $crit, $dir, $vals]) {
            $kpi = Kpi::create([
                'reference' => "KPI $year " . sprintf('%04d', $i++), 'name' => $name, 'area' => $area,
                'standard' => ['quality' => 'ISO 9001', 'environment' => 'ISO 14001', 'safety' => 'ISO 45001', 'food_safety' => 'ISO 22000'][$area] ?? null,
                'unit' => $unit, 'target_value' => $target, 'warning_threshold' => $warn, 'critical_threshold' => $crit,
                'direction' => $dir, 'aggregation' => 'monthly', 'related_site' => 'Plant A', 'status' => 'active', 'created_by' => $u,
            ]);
            foreach ($vals as $m => $v) {
                $kpi->results()->create([
                    'period_label' => sprintf('%s-%02d', $year, $m + 1),
                    'period_date' => sprintf('%s-%02d-28', $year, $m + 1),
                    'value' => $v, 'recorded_by' => $u,
                ]);
            }
        }
        $this->command->info('Seeded 5 KPIs with monthly results.');
    }
}
