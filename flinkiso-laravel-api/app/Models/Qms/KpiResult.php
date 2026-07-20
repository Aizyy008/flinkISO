<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KpiResult extends Model
{
    use HasUuids;

    protected $table = 'qms_kpi_results';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = ['value' => 'decimal:4', 'period_date' => 'date'];

    public function kpi()
    {
        return $this->belongsTo(Kpi::class, 'kpi_id');
    }
}
