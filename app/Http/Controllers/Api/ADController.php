<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/20
 * Time: 9:47 AM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ADSignUp;
use Illuminate\Http\Request;

class ADController extends Controller
{
    public function activitySignUp(Request $request)
    {
        $uid = $request->user()->id;
        $ad_id = request('ad_id');
        $count = ADSignUp::where([['ad_id', $ad_id], ['user_id', $uid]])->count();
        if ($count >= 20) {
            return response()->json(array('code' => 202));
        } else if ($count > 0) {
            return response()->json(array('code' => 201));
        } else {
            $adSignUp = ADSignUp::create(['ad_id' => $ad_id, 'user_id' => $uid]);
            return response()->json(array('code' => 200));
        }
    }
}
