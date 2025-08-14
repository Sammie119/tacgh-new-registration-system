<?php

namespace App\Models\Admin;

use App\Models\Registrant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlinePayment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function registrant()
    {
        return $this->belongsTo(Registrant::class, 'reg_id');
    }
}
