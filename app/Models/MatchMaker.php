<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/10
 * Time: 4:50 PM
 * 
 * 红娘功能的信息
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatchMaker extends Model {

	use SoftDeletes;
	protected $table = 'matchmaker';
	protected $dates = [ 'deleted_at' ];
	public $timestamps = true;
	protected $guarded = array();
}