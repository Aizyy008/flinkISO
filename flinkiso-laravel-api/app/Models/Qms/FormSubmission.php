<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    use HasUuids;

    protected $table = 'qms_form_submissions';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = ['data' => 'array'];

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }
}
