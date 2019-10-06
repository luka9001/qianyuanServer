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

class SocialMessage extends Model {

	use SoftDeletes;
	protected $table = 'social_message';
	protected $dates = [ 'deleted_at' ];
	public $timestamps = true;
	protected $guarded = array();
	protected $touches = [ 'User' ];//更新父模型时间戳

	public function user() {
		return $this->belongsTo( 'App\User' );
	}

	public function likes(){
		return $this->hasMany('App\Models\Likes');
	}

	public function comments(){
		return $this->hasMany('App\Models\Comment');
	}
}