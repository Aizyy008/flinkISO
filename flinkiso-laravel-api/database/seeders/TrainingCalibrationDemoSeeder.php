<?php

namespace Database\Seeders;

use App\Models\FlinkUser;
use App\Models\Qms\Asset;
use App\Models\Qms\Calibration;
use App\Models\Qms\Training;
use App\Models\Qms\TrainingRecord;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Sample Training & Competency and Asset & Calibration data for the demo
 * (dairy / food-safety context). Idempotent.
 */
class TrainingCalibrationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = FlinkUser::where('soft_delete', 0)->first();
        $uid = $user?->id;

        if (!Training::where('reference', 'like', 'TRN %')->exists()) {
            $trainings = [
                ['reference' => 'TRN ' . date('Y') . ' 0001', 'title' => 'Food Hygiene Level 2', 'category' => 'Food Safety', 'validity_months' => 12, 'mandatory' => true],
                ['reference' => 'TRN ' . date('Y') . ' 0002', 'title' => 'HACCP Awareness', 'category' => 'Food Safety', 'validity_months' => 24, 'mandatory' => true],
                ['reference' => 'TRN ' . date('Y') . ' 0003', 'title' => 'GMP & Personal Hygiene', 'category' => 'Quality', 'validity_months' => 12, 'mandatory' => true],
                ['reference' => 'TRN ' . date('Y') . ' 0004', 'title' => 'Allergen Management', 'category' => 'Food Safety', 'validity_months' => null, 'mandatory' => false],
            ];
            foreach ($trainings as $t) {
                $model = Training::create($t + ['created_by' => $uid, 'description' => $t['title'] . ' training course.']);
                // One assigned + completed record so the competency matrix is populated.
                TrainingRecord::create([
                    'training_id' => $model->id, 'user_id' => $uid, 'status' => 'completed',
                    'completed_date' => now()->subMonths(2)->toDateString(),
                    'expiry_date' => $model->validity_months ? now()->subMonths(2)->addMonths($model->validity_months)->toDateString() : null,
                    'result' => 'Passed', 'created_by' => $uid,
                ]);
            }
            $this->command->info('Seeded 4 training courses with competency records.');
        } else {
            $this->command->info('Training demo data already exists, skipping.');
        }

        if (!Asset::where('reference', 'like', 'AST %')->exists()) {
            $assets = [
                ['name' => 'Digital Thermometer TH-01', 'asset_type' => 'Measuring', 'location' => 'Pasteurizer Line 1', 'serial_no' => 'TH-01-2024', 'freq' => 6],
                ['name' => 'Pasteurizer Temp Probe', 'asset_type' => 'Measuring', 'location' => 'Pasteurizer', 'serial_no' => 'PP-88', 'freq' => 6],
                ['name' => 'Platform Weighing Scale', 'asset_type' => 'Weighing', 'location' => 'Goods In', 'serial_no' => 'WS-500', 'freq' => 12],
                ['name' => 'pH Meter', 'asset_type' => 'Measuring', 'location' => 'QA Lab', 'serial_no' => 'PH-220', 'freq' => 3],
            ];
            $i = 1;
            foreach ($assets as $a) {
                $performed = Carbon::now()->subMonths(1);
                $nextDue = $performed->copy()->addMonths($a['freq']);
                $asset = Asset::create([
                    'reference' => 'AST ' . date('Y') . ' ' . sprintf('%04d', $i++),
                    'name' => $a['name'], 'asset_type' => $a['asset_type'], 'location' => $a['location'],
                    'serial_no' => $a['serial_no'], 'requires_calibration' => true,
                    'calibration_frequency_months' => $a['freq'], 'next_due_date' => $nextDue->toDateString(),
                    'status' => 'active', 'created_by' => $uid,
                ]);
                Calibration::create([
                    'asset_id' => $asset->id, 'performed_date' => $performed->toDateString(),
                    'result' => 'pass', 'performed_by' => 'QA Technician',
                    'next_due_date' => $nextDue->toDateString(), 'notes' => 'Calibrated against reference standard.',
                    'created_by' => $uid,
                ]);
            }
            $this->command->info('Seeded 4 calibrated assets.');
        } else {
            $this->command->info('Asset demo data already exists, skipping.');
        }
    }
}
