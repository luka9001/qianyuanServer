<?php
namespace App\Http\Utils;

use Carbon\Carbon;

class VipStatus
{
    public static function isVipNow($vip_end_time)
    {
        return Carbon::parse($vip_end_time)->gt(Carbon::now());
    }
}
