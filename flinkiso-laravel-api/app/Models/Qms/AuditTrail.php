<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Model;

/**
 * Append-only audit record. Primary key is the auto-increment `seq`
 * (guarantees strict ordering for the hash chain); `id` is a UUID reference.
 */
class AuditTrail extends Model
{
    protected $table = 'qms_audit_trail';
    protected $primaryKey = 'seq';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];
}
