<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemInfo extends Model
{
	use SoftDeletes;
	protected $table = 'system_info';
	protected $dates = [ 'deleted_at' ];
	public $timestamps = true;
	protected $guarded = array();
}
