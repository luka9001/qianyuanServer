<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class AdminUser extends Authenticatable
{
    use Notifiable, HasApiTokens;
    // protected $table = 'admin_users';
    // protected $dates = ['deleted_at'];
    // public $timestamps = true;
    // protected $guarded = array();

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $fillable = [
        'name', 'mobile', 'password',
    ];
}
