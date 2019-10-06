<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function favorites()
    {
        return $this->hasMany('App\Models\Favorites');
    }

    public function favoriteme()
    {
        return $this->hasMany('App\Models\Favoriteme');
    }

    public function social_messages()
    {
        return $this->hasMany('App\Models\SocialMessage');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }
}
