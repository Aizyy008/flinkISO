<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasUuids;

    protected $table = 'qms_documents';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    /** Allowed lifecycle transitions: from => [to...]. */
    public const TRANSITIONS = [
        'draft' => ['review'],
        'review' => ['approved', 'draft'],
        'approved' => ['released'],
        'released' => ['obsolete'],
        'obsolete' => [],
    ];

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class, 'document_id');
    }

    public function changeRequests()
    {
        return $this->hasMany(ChangeRequest::class, 'document_id');
    }

    public function controlledCopies()
    {
        return $this->hasMany(ControlledCopy::class, 'document_id');
    }
}
