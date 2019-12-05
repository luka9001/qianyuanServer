<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/10
 * Time: 4:50 PM
 *
 * 广告和官方活动信息
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivitiesAD extends Model
{
    use SoftDeletes;
    protected $table = 'activities_ad';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $guarded = array();
}
