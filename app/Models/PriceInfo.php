<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/10
 * Time: 4:50 PM
 * 会员购买的各种平台服务的定价
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceInfo extends Model {

	use SoftDeletes;
	protected $table = 'price_info';
	protected $dates = [ 'deleted_at' ];
	public $timestamps = true;
	protected $guarded = array();
}