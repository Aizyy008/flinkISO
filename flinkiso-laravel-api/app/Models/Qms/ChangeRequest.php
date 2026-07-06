<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ChangeRequest extends Model
{
    use HasUuids;

    protected $table = 'qms_change_requests';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];
}
