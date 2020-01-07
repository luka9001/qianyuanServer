<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermissionGroup extends Model
{
    use SoftDeletes;
    protected $table = 'permission_group';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $guarded = array();
}
