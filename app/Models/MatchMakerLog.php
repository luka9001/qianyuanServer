<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatchMakerLog extends Model
{
    use SoftDeletes;
    protected $table = 'matchmaker_log';
    protected $dates = [ 'deleted_at' ];
    public $timestamps = true;
    protected $guarded = array();
}
