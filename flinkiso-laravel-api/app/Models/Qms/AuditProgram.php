<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AuditProgram extends Model
{
    use HasUuids;

    protected $table = 'qms_audit_programs';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = ['year' => 'integer'];

    public function audits()
    {
        return $this->hasMany(Audit::class, 'program_id');
    }
}
