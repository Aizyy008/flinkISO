<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasUuids;

    protected $table = 'qms_incidents';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'detected_date' => 'date',
    ];

    public function capas()
    {
        return $this->hasMany(Capa::class, 'incident_id');
    }
}
