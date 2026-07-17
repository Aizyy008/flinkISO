<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * A 21 CFR Part 11 electronic signature bound to a specific document action.
 * Append-only in practice — signatures are never edited from the UI.
 */
class ElectronicSignature extends Model
{
    use HasUuids;

    protected $table = 'qms_electronic_signatures';

    protected $guarded = [];

    protected $casts = [
        'signed_at' => 'datetime',
        'document_version' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'entity_id');
    }
}
