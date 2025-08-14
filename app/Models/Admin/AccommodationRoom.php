<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccommodationRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function block()
    {
        return $this->belongsTo(AccommodationBlock::class, 'block_id');
    }

    public function resident()
    {
        return $this->belongsTo(Accommodation::class, 'residence_id');
    }

}
