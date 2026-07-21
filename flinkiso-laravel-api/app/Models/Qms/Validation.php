<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
    use HasUuids;

    protected $table = 'qms_validations';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'performed_date' => 'date',
        'valid_until' => 'date',
    ];

    public const TYPES = [
        'equipment_iq' => 'Equipment IQ (Installation)',
        'equipment_oq' => 'Equipment OQ (Operational)',
        'equipment_pq' => 'Equipment PQ (Performance)',
        'process' => 'Process validation',
        'cleaning' => 'Cleaning validation',
        'computer_system' => 'Computer system validation',
        'method' => 'Method validation',
        'other' => 'Other',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /** Revalidation status derived from valid_until. */
    public function revalidationStatus(): string
    {
        if (! $this->valid_until) {
            return 'n/a';
        }
        $days = now()->startOfDay()->diffInDays($this->valid_until->copy()->startOfDay(), false);
        if ($days < 0) return 'expired';
        if ($days <= 30) return 'due';
        return 'valid';
    }
}
