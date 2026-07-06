<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkflowRun extends Model
{
    use HasUuids;

    protected $table = 'qms_workflow_runs';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'result' => 'array',
        'created_at' => 'datetime',
    ];
}
