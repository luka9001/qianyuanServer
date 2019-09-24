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

class Favoriteme extends Model {

	use SoftDeletes;
	protected $table = 'favoriteme';
	protected $dates = [ 'deleted_at' ];
	public $timestamps = true;
	protected $guarded = array();
	protected $touches = [ 'User' ];//更新父模型时间戳

	public function user() {
		return $this->belongsTo( 'App\User' );
	}
}