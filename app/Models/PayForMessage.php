<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/10
 * Time: 4:50 PM
 * 
 * 购买了哪个用户的聊天权限
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayForMessage extends Model
{

    use SoftDeletes;
    protected $table = 'pay_for_message';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $guarded = array();
}
