<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
//    protected $fillable = ['user_id','title','description','slug','is_public'];

    protected static function booted()
    {
        static::creating(function ($form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->title).'-'.Str::random(6);
            }
        });
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function fields() {
        return $this->hasMany(FormField::class)->orderBy('order');
    }
    public function responses() {
        return $this->hasMany(FormResponse::class);
    }
}
