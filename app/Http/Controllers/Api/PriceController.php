<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayForMessage;
use App\Models\Price;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceController extends Controller
{
    /**
     * 我的心动币界面数据
     */
    public function getCoin(Request $request)
    {
        $user = $request->user();

        $info = DB::table('users')->where('id', $user->id)->select('nreligion', 'noccupation', 'nhouse', 'ncar', 'nincome', 'nbaby', 'ndrink', 'nsmoke', 'nmarrystatus', 'nbirthplace', 'nlive', 'education', 'smoke', 'drink', 'baby', 'detail', 'income', 'car', 'house', 'occupation', 'religion', 'hobby', 'nsex', 'nbirthdate', 'nheight', 'nmaxheight', 'neducation')->get()->toArray();
        $allInfoStatus = true;
        foreach ($info[0] as $k => $v) {
            if ($v === null) {
                $allInfoStatus = false;
                break;
            }
        }

        $fCount = $user->favorites()->count();
        $data['favorites'] = $fCount;
        $data['social_messages'] = $user->social_messages()->count();
        if ($user->price()->count() > 0) {
            $data['coin'] = $user->price()->first()->coin;
        }
        $data['allInfoStatus'] = $allInfoStatus;

        return response()->json(array('code' => 200, 'data' => $data));
    }

    /**
     * 支付心动币购买聊天权限
     */
    public function payForMessage(Request $request)
    {
        $price = Price::where('user_id', $request->user()->id)->first();
        $price->coin = $price->coin - 100;
        $price->save();

        PayForMessage::create([
            'user_id' => $request->user()->id,
            'message_uid' => request('message_uid')
        ]);

        return response()->json(array('code' => 200));
    }
}
