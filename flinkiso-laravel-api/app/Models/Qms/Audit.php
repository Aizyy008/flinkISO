<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasUuids;

    protected $table = 'qms_audits';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'planned_date' => 'date',
        'actual_date' => 'date',
    ];

    public function program()
    {
        return $this->belongsTo(AuditProgram::class, 'program_id');
    }

    public function checklistItems()
    {
        return $this->hasMany(AuditChecklistItem::class, 'audit_id')->orderBy('sort_order');
    }

    public function findings()
    {
        return $this->hasMany(AuditFinding::class, 'audit_id');
    }
}
