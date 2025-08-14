<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormResponseValue extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
//    protected $fillable = ['response_id','field_id','value'];

    public function response() {
        return $this->belongsTo(FormResponse::class, 'response_id');
    }

    public function field() {
        return $this->belongsTo(FormField::class);
    }

}
