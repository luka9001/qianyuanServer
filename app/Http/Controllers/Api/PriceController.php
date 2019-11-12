<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayForMessage;
use App\Models\PayInfo;
use App\Models\Price;
use App\Models\PriceInfo;
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
        $price->coin = $price->coin - request('payCoin');
        $price->save();

        PayForMessage::create([
            'user_id' => $request->user()->id,
            'message_uid' => request('message_uid'),
        ]);

        return response()->json(array('code' => 200));
    }

    /**
     * 红娘费用信息
     */
    public function matckMakerPriceInfo(Request $request)
    {
        $price = Price::where('user_id', $request->user()->id)->first();
        if ($price != null) {
            $data['vip_level'] = $price->vip_level;
            $data['coin'] = $price->coin;
            $data['vip_start_time'] = $price->vip_start_time;
        }

        $priceInfo = PriceInfo::where('type', 2)->first();
        $data['payCoin'] = $priceInfo->coin;

        return response()->json(array('code' => 200, 'data' => $data));
    }

    /**
     * 心动币打折信息
     */
    public function coinDiscount(Request $request)
    {
        $priceInfo = PriceInfo::where('type', 4)->first();
        $data['coinDiscount'] = $priceInfo->coin;
        $payInfo = PayInfo::where('type', '<=', 2)->get();
        $data['payInfo'] = $payInfo;

        return response()->json(array('code' => 200, 'data' => $data));
    }

    /**
     * 会员打折信息
     */
    public function vipDisCount(Request $request)
    {
        $priceInfo = PriceInfo::where('type', 3)->first();
        $data['coinDiscount'] = $priceInfo->coin;
        $payInfo = PayInfo::where([['type', '>=', 3], ['type', '<=', 5]])->get();
        $data['payInfo'] = $payInfo;
        $price = Price::where('user_id', $request->user()->id)->first();
        $data['vip_level'] = $price->vip_level;
        $data['vip_start_time'] = $price->vip_start_time;

        return response()->json(array('code' => 200, 'data' => $data));
    }

    /**
     * 获取心动币支付价格信息
     */
    public function getCoinPayInfo(Request $request)
    {
        $payInfo = PayInfo::where('type', '<=', 2)->get();

        return response()->json(array('code' => 200, 'data' => $payInfo));
    }

    /**
     * 获取会员价格信息
     */
    public function getVipPayInfo(Request $request)
    {
        $payInfo = PayInfo::where([['type', '>=', 3], ['type', '<=', 5]])->get();

        return response()->json(array('code' => 200, 'data' => $payInfo));
    }
}
