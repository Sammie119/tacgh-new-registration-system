<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormField extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
//    protected $fillable = ['form_id','label','field_type','options','is_required','order'];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public function form() {
        return $this->belongsTo(Form::class);
    }
}
