<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registrant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'registrants';

    protected $guarded = [];

    public function stage()
    {
        return $this->belongsTo(RegistrantStage::class, 'stage_id');
    }
}
