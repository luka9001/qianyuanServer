<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PayForMessage;
use App\Models\PayInfo;
use App\Models\Price;
use App\Models\PriceInfo;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use App\Http\Utils\VipStatus;

ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

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
            $data['vip_level'] = VipStatus::isVipNow($price->vip_end_time) ? $price->vip_level : 0;
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
        $data['vip_level'] = VipStatus::isVipNow($price->vip_end_time) ? $price->vip_level : 0;;
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

    /**
     * 处理Paypal付款结果
     * order_id：第三方支付平台的订单号,price：产品价格,product:产品名称,product_type：产品类型
     */
    public function payPal(Request $request)
    {
        $orderId = request('order_id');
        $price = request('price');
        $product = request('product');
        $product_type = request('product_type');

        $order = Order::create([
            'user_id' => $request->user()->id,
            'order_id' => $orderId,
            'price' => $price,
            'product' => $product,
            'product_type' => $product_type,
        ]);

        $userPriceInfo = Price::where('user_id', $request->user()->id)->first();

        $payInfo = PayInfo::where('type', $product_type)->first();
        Log::info($payInfo->product_content);
        //小于3表示购买的心动币
        if ($product_type < 3) {
            if ($userPriceInfo != null) {
                $userPriceInfo->coin = $userPriceInfo->coin + $payInfo->product_content;
                $userPriceInfo->save();
            } else {
                Price::create(['user_id' => $request->user()->id, 'coin' => $payInfo->product_content, 'vip_level' => 0]);
            }
        } else {
            if ($userPriceInfo != null) {
                $userPriceInfo->vip_level = $payInfo->product_content;
                $userPriceInfo->coin = $userPriceInfo->coin + self::vipCoin($payInfo->product_content);
                $userPriceInfo->vip_start_time = $order->created_at;
                $userPriceInfo->vip_end_time = self::vipEndTime($payInfo->product_content, $order->created_at);
                $userPriceInfo->save();
            } else {
                Price::create(['user_id' => $request->user()->id, 'coin' => self::vipCoin($payInfo->product_content), 'vip_level' => $payInfo->product_content, 'vip_start_time' => $order->created_at]);
            }
        }

        // $client = self::client();

        // $response = $client->execute(new OrdersGetRequest($orderId));

        return response()->json(array('code' => 200));
    }

    //充值会员赠送的金币
    public function vipCoin($vip_level)
    {
        $matchMakerCoin = PriceInfo::where('type', 1)->first()->coin;
        switch ($vip_level) {
            case 1:
                return $matchMakerCoin;
            case 2:
                return $matchMakerCoin * 10;
            case 3:
                return $matchMakerCoin * 25;
            default:
                return 0;
        }
    }

    public function vipEndTime($vip_level, $vip_start_time)
    {
        switch ($vip_level) {
            //一个月
            case 1:
                return Carbon::parse($vip_start_time)->addMonths(1)->toDateTimeString();
            //半年
            case 2:
                return Carbon::parse($vip_start_time)->addMonths(6)->toDateTimeString();
            //一年
            case 3:
                return Carbon::parse($vip_start_time)->addYears(1)->toDateTimeString();
        }
    }

    public static function client()
    {
        return new PayPalHttpClient(self::environment());
    }

    /**
     * Setting up and Returns PayPal SDK environment with PayPal Access credentials.
     * For demo purpose, we are using SandboxEnvironment. In production this will be
     * ProductionEnvironment.
     */
    public static function environment()
    {
        $clientId = getenv("CLIENT_ID");
        $clientSecret = getenv("CLIENT_SECRET");
        return new SandboxEnvironment($clientId, $clientSecret);
    }
}
