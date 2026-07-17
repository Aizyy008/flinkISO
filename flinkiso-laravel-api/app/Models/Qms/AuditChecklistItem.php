<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AuditChecklistItem extends Model
{
    use HasUuids;

    protected $table = 'qms_audit_checklist_items';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = ['sort_order' => 'integer'];

    public function audit()
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }
}
