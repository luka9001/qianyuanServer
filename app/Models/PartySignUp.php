<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartySignUp extends Model
{
    use SoftDeletes;
    protected $table = 'party_signup';
    protected $dates = [ 'deleted_at' ];
    public $timestamps = true;
    protected $guarded = array();
}
