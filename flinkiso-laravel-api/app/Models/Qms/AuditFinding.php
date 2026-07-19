<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AuditFinding extends Model
{
    use HasUuids;

    protected $table = 'qms_audit_findings';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function audit()
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class, 'incident_id');
    }
}
