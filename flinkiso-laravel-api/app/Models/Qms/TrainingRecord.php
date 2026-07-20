<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TrainingRecord extends Model
{
    use HasUuids;

    protected $table = 'qms_training_records';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];
    protected $casts = [
        'completed_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id');
    }

    /** Live competency status: assigned, valid, expiring (<=30 days), or expired. */
    public function competency(): string
    {
        if ($this->status === 'assigned' || !$this->completed_date) {
            return 'assigned';
        }
        if (!$this->expiry_date) {
            return 'valid';
        }
        if ($this->expiry_date->isPast()) {
            return 'expired';
        }
        if (now()->startOfDay()->diffInDays($this->expiry_date->copy()->startOfDay(), false) <= 30) {
            return 'expiring';
        }
        return 'valid';
    }
}
