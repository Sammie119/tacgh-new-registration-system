<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignPermissionToRole extends Model
{
    use HasFactory;

    protected $table = "assign_permission_to_roles";
    protected $guarded = [];
}
