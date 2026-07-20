<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HaccpCcp extends Model
{
    use HasUuids;

    protected $table = 'qms_haccp_ccps';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'limit_min' => 'float',
        'limit_max' => 'float',
    ];

    public function plan()
    {
        return $this->belongsTo(HaccpPlan::class, 'plan_id');
    }

    public function step()
    {
        return $this->belongsTo(HaccpStep::class, 'step_id');
    }

    public function logs()
    {
        return $this->hasMany(HaccpCcpLog::class, 'ccp_id')->latest('measured_at');
    }

    /** True when a measured value is within the CCP's numeric critical limits. */
    public function isWithinLimit(?float $value): bool
    {
        if ($value === null) {
            return true; // no numeric reading to check
        }
        if ($this->limit_min !== null && $value < $this->limit_min) {
            return false;
        }
        if ($this->limit_max !== null && $value > $this->limit_max) {
            return false;
        }
        return true;
    }
}
