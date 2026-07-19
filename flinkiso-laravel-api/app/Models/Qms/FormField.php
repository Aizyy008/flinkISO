<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasUuids;

    protected $table = 'qms_form_fields';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }
}
