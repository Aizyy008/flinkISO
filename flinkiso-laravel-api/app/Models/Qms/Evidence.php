<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{
    use HasUuids;

    protected $table = 'qms_evidence';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'json_data' => 'array',
        'record_date' => 'datetime',
    ];
}
