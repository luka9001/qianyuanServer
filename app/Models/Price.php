<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/10
 * Time: 4:50 PM
 * 用户已经购买的心动币和会员
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model {

	use SoftDeletes;
	protected $table = 'price';
	protected $dates = [ 'deleted_at' ];
	public $timestamps = true;
	protected $guarded = array();
	protected $touches = [ 'User' ];//更新父模型时间戳

	public function user() {
		return $this->belongsTo( 'App\User' );
	}
}