<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ControlledCopy extends Model
{
    use HasUuids;

    protected $table = 'qms_controlled_copies';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
    ];
}
