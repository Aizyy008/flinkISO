<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HaccpStep extends Model
{
    use HasUuids;

    protected $table = 'qms_haccp_steps';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function plan()
    {
        return $this->belongsTo(HaccpPlan::class, 'plan_id');
    }
}
