<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    use HasUuids;

    protected $table = 'qms_document_versions';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];
}
