<?php

namespace Database\Seeders;

use App\Models\FlinkUser;
use App\Models\Qms\HaccpCcp;
use App\Models\Qms\HaccpHazard;
use App\Models\Qms\HaccpPlan;
use App\Models\Qms\HaccpStep;
use Illuminate\Database\Seeder;

/**
 * Sample HACCP plan from the client's reference product (Fresh Milk 3.5%),
 * with the pasteurization CCP (72C for 15 sec). Idempotent.
 */
class HaccpDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (HaccpPlan::where('product', 'Fresh Milk 3.5%')->exists()) {
            $this->command->info('Demo HACCP plan already exists, skipping.');
            return;
        }

        $user = FlinkUser::where('soft_delete', 0)->first();

        $plan = HaccpPlan::create([
            'reference' => 'HACCP ' . date('Y') . ' 0001',
            'product' => 'Fresh Milk 3.5%',
            'description' => 'Pasteurised fresh milk, 3.5% fat.',
            'status' => 'active',
            'team' => 'QA Manager, Production Lead, Food Safety Officer',
            'created_by' => $user?->id,
            'approved_by' => $user?->id,
            'approved_date' => now()->toDateString(),
        ]);

        $steps = ['Receiving', 'Storage', 'Pasteurization', 'Cooling', 'Packaging', 'Warehouse', 'Shipping'];
        $stepModels = [];
        foreach ($steps as $i => $name) {
            $stepModels[$name] = HaccpStep::create(['plan_id' => $plan->id, 'seq' => $i + 1, 'name' => $name]);
        }

        // Hazards on pasteurization (the CCP step)
        foreach ([
            ['biological', 'Pathogen survival (e.g. Listeria) if under-processed', 'high', 'Pasteurise at correct time/temperature', 'CCP'],
            ['chemical', 'Cleaning chemical residue', 'medium', 'Rinse verification', 'OPRP'],
            ['physical', 'Foreign matter', 'low', 'Inline filter / sieve', 'PRP'],
            ['allergen', 'Milk allergen cross-contact', 'medium', 'Dedicated line / labelling', 'PRP'],
        ] as [$type, $desc, $sig, $ctrl, $ctype]) {
            HaccpHazard::create([
                'plan_id' => $plan->id,
                'step_id' => $stepModels['Pasteurization']->id,
                'hazard_type' => $type,
                'description' => $desc,
                'significance' => $sig,
                'control_measure' => $ctrl,
                'control_type' => $ctype,
            ]);
        }

        // CCP: pasteurization, 72C for 15 sec -> numeric lower bound 72
        HaccpCcp::create([
            'plan_id' => $plan->id,
            'step_id' => $stepModels['Pasteurization']->id,
            'name' => 'Pasteurization',
            'critical_limit' => '72C for 15 sec',
            'limit_min' => 72,
            'limit_max' => null,
            'monitor_what' => 'Temperature',
            'monitor_how' => 'Inline probe',
            'monitor_frequency' => 'Each batch',
            'responsible' => 'Pasteuriser operator',
            'corrective_action' => 'Divert/hold batch, re-pasteurise, investigate, verify.',
        ]);

        $this->command->info('Seeded demo HACCP plan for Fresh Milk 3.5% with pasteurization CCP.');
    }
}
