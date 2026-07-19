<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasUuids;

    protected $table = 'qms_forms';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function fields()
    {
        return $this->hasMany(FormField::class, 'form_id')->orderBy('sort_order');
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class, 'form_id')->latest();
    }
}
