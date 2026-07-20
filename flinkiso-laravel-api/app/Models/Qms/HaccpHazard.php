<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HaccpHazard extends Model
{
    use HasUuids;

    protected $table = 'qms_haccp_hazards';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public const TYPES = ['biological', 'chemical', 'physical', 'allergen'];
    public const CONTROL_TYPES = ['PRP', 'OPRP', 'CCP'];

    public function step()
    {
        return $this->belongsTo(HaccpStep::class, 'step_id');
    }
}
