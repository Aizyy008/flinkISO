<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * QMS document-workflow roles for a legacy user (Creator / Reviewer / Approver /
 * Publisher). Stored in our database so the legacy users table stays read-only.
 */
class UserRole extends Model
{
    use HasUuids;

    protected $table = 'qms_user_roles';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'is_creator' => 'boolean',
        'is_reviewer' => 'boolean',
        'is_approver' => 'boolean',
        'is_publisher' => 'boolean',
    ];
}
