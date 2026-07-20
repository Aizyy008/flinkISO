<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasUuids;

    protected $table = 'qms_assets';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];
    protected $casts = [
        'requires_calibration' => 'boolean',
        'next_due_date' => 'date',
    ];

    public function calibrations()
    {
        return $this->hasMany(Calibration::class, 'asset_id')->latest('performed_date');
    }

    /** Calibration status: ok, due (<=30 days), overdue, or n/a. */
    public function calibrationStatus(): string
    {
        if (!$this->requires_calibration) {
            return 'n/a';
        }
        if (!$this->next_due_date) {
            return 'due';
        }
        if ($this->next_due_date->isPast()) {
            return 'overdue';
        }
        if (now()->startOfDay()->diffInDays($this->next_due_date->copy()->startOfDay(), false) <= 30) {
            return 'due';
        }
        return 'ok';
    }
}
