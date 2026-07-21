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
        'due_date' => 'date',
        'iso_overlay' => 'array',
    ];

    public const TYPES = ['non_conformity', 'deviation', 'incident', 'complaint', 'near_miss'];
    public const SEVERITIES = ['low', 'medium', 'high', 'critical'];
    public const STATUSES = ['open', 'investigating', 'capa_raised', 'closed'];

    public function capas()
    {
        return $this->hasMany(Capa::class, 'incident_id');
    }
}
