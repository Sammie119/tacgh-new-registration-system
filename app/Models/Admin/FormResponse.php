<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormResponse extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
//    protected $fillable = ['form_id','submitter_name','submitter_email','submitter_ip'];

    public function form() {
        return $this->belongsTo(Form::class);
    }

    public function values() {
        return $this->hasMany(FormResponseValue::class, 'response_id');
    }
}
