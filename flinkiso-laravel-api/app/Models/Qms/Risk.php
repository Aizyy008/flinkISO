<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Risk extends Model
{
    use HasUuids;

    protected $table = 'qms_risks';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'likelihood' => 'integer',
        'severity' => 'integer',
        'detection' => 'integer',
        'risk_score' => 'integer',
    ];

    /** Recalculate score + level from likelihood/severity/detection. */
    public function recalculate(): void
    {
        $this->risk_score = (int) $this->likelihood * (int) $this->severity * (int) $this->detection;
        $this->risk_level = match (true) {
            $this->risk_score >= 60 => 'critical',
            $this->risk_score >= 30 => 'high',
            $this->risk_score >= 10 => 'medium',
            default => 'low',
        };
    }
}
