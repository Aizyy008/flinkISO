<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Capa extends Model
{
    use HasUuids;

    protected $table = 'qms_capa';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'effectiveness_verified' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class, 'incident_id');
    }
}
