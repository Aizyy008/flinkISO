<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HaccpPlan extends Model
{
    use HasUuids;

    protected $table = 'qms_haccp_plans';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];
    protected $casts = ['approved_date' => 'date'];

    public function steps()
    {
        return $this->hasMany(HaccpStep::class, 'plan_id')->orderBy('seq');
    }

    public function hazards()
    {
        return $this->hasMany(HaccpHazard::class, 'plan_id');
    }

    public function ccps()
    {
        return $this->hasMany(HaccpCcp::class, 'plan_id');
    }
}
