<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Party extends Model
{
    use SoftDeletes;
    protected $table = 'party';
    protected $dates = [ 'deleted_at' ];
    public $timestamps = true;
    protected $guarded = array();
}
