<?php

namespace App\Models\Qms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasUuids;

    protected $table = 'qms_trainings';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];
    protected $casts = ['mandatory' => 'boolean'];

    public function records()
    {
        return $this->hasMany(TrainingRecord::class, 'training_id');
    }
}
