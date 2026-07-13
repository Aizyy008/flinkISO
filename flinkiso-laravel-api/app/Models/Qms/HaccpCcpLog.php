<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HaccpCcpLog extends Model
{
    use HasUuids;

    protected $table = 'qms_haccp_ccp_logs';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'measured_value' => 'float',
        'measured_at' => 'datetime',
        'within_limit' => 'boolean',
    ];

    public function ccp()
    {
        return $this->belongsTo(HaccpCcp::class, 'ccp_id');
    }

    public function capa()
    {
        return $this->belongsTo(Capa::class, 'capa_id');
    }
}
