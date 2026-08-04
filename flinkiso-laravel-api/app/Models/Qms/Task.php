<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * A real, assignable QMS task — created by the workflow "assign a task" and
 * "request an approval" actions. Held in our database; the assignee is a legacy
 * users.id. Appears on the assignee's "My Tasks" list and can be completed.
 */
class Task extends Model
{
    use HasUuids;

    protected $table = 'qms_tasks';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];
}
