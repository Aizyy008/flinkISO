<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    use HasUuids;

    protected $table = 'qms_kpis';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'target_value' => 'decimal:4',
        'warning_threshold' => 'decimal:4',
        'critical_threshold' => 'decimal:4',
        'zaikpi_synced_at' => 'datetime',
    ];

    public function results()
    {
        return $this->hasMany(KpiResult::class, 'kpi_id')->orderBy('period_date');
    }

    public function latestResult()
    {
        return $this->hasOne(KpiResult::class, 'kpi_id')->latestOfMany('period_date');
    }

    /** Evaluate a value against target + thresholds and direction. */
    public function statusFor($value): string
    {
        if ($value === null) {
            return 'no_data';
        }
        $v = (float) $value;
        $crit = $this->critical_threshold;
        $warn = $this->warning_threshold;
        if ($this->direction === 'lower_better') {
            if ($crit !== null && $v > (float) $crit) return 'critical';
            if ($warn !== null && $v > (float) $warn) return 'warning';
        } else { // higher_better
            if ($crit !== null && $v < (float) $crit) return 'critical';
            if ($warn !== null && $v < (float) $warn) return 'warning';
        }
        return 'on_target';
    }

    /** Percent of target achieved (for the gauge). */
    public function achievement($value): ?float
    {
        if ($value === null || !$this->target_value || (float) $this->target_value == 0.0) {
            return null;
        }
        $v = (float) $value; $t = (float) $this->target_value;
        $pct = $this->direction === 'lower_better' ? ($t / max($v, 0.0001)) * 100 : ($v / $t) * 100;
        return round(min($pct, 150), 1);
    }
}
