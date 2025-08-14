<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistrantStage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'registrants_stage';

    protected $guarded = [];

    public function stage()
    {
        return $this->hasOne(Registrant::class, 'stage_id', 'id');
    }
}
