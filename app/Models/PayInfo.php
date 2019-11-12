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

class PayInfo extends Model
{
    use SoftDeletes;
    protected $table = 'pay_info';
    protected $dates = ['deleted_at'];
    public $timestamps = true;
    protected $guarded = array();

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];
}
