<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/10
 * Time: 4:50 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model {

	use SoftDeletes;
	protected $table = 'report';
	protected $dates = [ 'deleted_at' ];
	public $timestamps = true;
	protected $guarded = array();
}