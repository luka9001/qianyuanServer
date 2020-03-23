<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Utils\VipStatus;
use App\Models\Admin;
use App\Models\MatchMaker;
use App\Models\Order;
use App\Models\Party;
use App\Models\PayInfo;
use App\Models\Price;
use App\Models\PriceInfo;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\User;

class UserController extends Controller
{
    //
    // use ProxyTrait;
    public $successStatus = 200;
    public $failStatus = 500;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function init(Request $request)
    {
        try {
            $unCheckUserCount = User::where('check_status', 0)->count();
            $unCheckPartyCount = Party::where('check_status', 0)->count();
            $unResolveMatchMakerCount = MatchMaker::where('status', 0)->count();
            $success['unCheckUserCount'] = $unCheckUserCount;
            $success['unCheckPartyCount'] = $unCheckPartyCount;
            $success['unResolveMatchMakerCount'] = $unResolveMatchMakerCount;
            return response()->json(['success' => $success], $this->successStatus);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception], $this->failStatus);
        }
    }

    /**
     *  注册
     * @param Request $request
     * @return JsonResponse
     */
    public function WxRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = Admin::createOrSave($input);
        $admin = Admin::where('username', $request->input('username'))->first();
        if ($user) {
            $success['token'] = $admin->createToken('api')->accessToken;
            $success['name'] = $input['username'];

            return response()->json(['success' => $success], $this->successStatus);
        } else {
            return response()->json(['file' => 'Information error'], $this->failStatus);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function WxLogin(Request $request)
    {
        $admin = Admin::where('username', $request->input('username'))->first();
        if ($admin && password_verify($request->input('password'), $admin->password)) {
            DB::table('oauth_access_tokens')->where('user_id', $admin->id)->where('name', 'admin')->update(['revoked' => 1]);
            $success['token'] = $admin->createToken('admin')->accessToken;
            return response()->json(['data' => $success], $this->successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    /**
     * 查看用户详情
     * @param Request $request
     * @return JsonResponse
     */
    public function WxDetails(Request $request)
    {
        // $user = Admin::where('id', $request->input('id'))->first();
        $user = $request->user()->username;
        return response()->json(['success' => $user], $this->successStatus);
    }

    /**
     * 根据未审核、审核、不通过各种状态获取用户列表
     */
    public function GetWaitForCheck()
    {
        $status = request('status');
        $user = User::where([['check_status', $status], ['state', 1]])->select('name', 'mobile', 'live', 'id')->orderBy('id', 'desc')->paginate(10);
        return response()->json(['success' => $user], $this->successStatus);
    }

    /**
     * 获取用户具体信息
     * @param Request $request
     * @return JsonResponse
     */
    public function memberDetail(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        $id = request('id');
        $user = User::find($id);

        $price = $user->price()->first();
        if ($price != null) {
            $user['vip_level'] = VipStatus::isVipNow($price->vip_end_time) ? $price->vip_level : 0;
            $user['vip_start_time'] = $price->vip_start_time;
            $user['coin'] = $price->coin;
        }

        return response()->json(['success' => $user], $this->successStatus);
    }

    /**
     * 提交审核结果
     */
    public function postCheckResult(Request $request)
    {
        $id = request('id');
        $status = request('status');
        $detail = request('detail');
        $user = User::find($id);
        $user->check_status = $status;
        if ($status === 2) {
            $user->check_detail = $detail;
        } else if ($status === 1) {
            $this->giveVip($id, $request->user()->id);
        }
        $user->save();
        return response()->json(['code' => 200]);
    }

    private function giveVip($uid, $admin_uid)
    {
        $orderId = $uid;
        $product = '官方赠送';

        $order = Order::create([
            'user_id' => $admin_uid,
            'order_id' => $orderId,
            'price' => 0,
            'product' => $product
        ]);

        $userPriceInfo = Price::where('user_id', $uid)->first();

        //此处为赠送新用户三天事件vip，但vip_level级别及图标设置为月度
        if ($userPriceInfo !== null) {
            $userPriceInfo->vip_level = 1;
            $userPriceInfo->vip_start_time = $order->created_at;
            $userPriceInfo->vip_end_time = self::vipEndTime(4, $order->created_at);
            $userPriceInfo->save();
        } else {
            Price::create(['user_id' => $uid, 'coin' => 0, 'vip_level' => 1, 'vip_start_time' => $order->created_at]);
        }
    }

    /**
     * 给用户vip
     * @param Request $request
     * @return JsonResponse
     */
    public function postGiveUserVip(Request $request)
    {
        $uid = request('uid');

        $orderId = $uid;
        $product = '官方赠送';
        $product_type = request('product_type');

        $order = Order::create([
            'user_id' => $request->user()->id,
            'order_id' => $orderId,
            'price' => 0,
            'product' => $product
        ]);

        $userPriceInfo = Price::where('user_id', $uid)->first();

        $payInfo = PayInfo::where('type', $product_type)->first();
        //小于3表示购买的心动币
        if ($product_type < 3) {
            if ($userPriceInfo != null) {
                $userPriceInfo->coin = $userPriceInfo->coin + $payInfo->product_content;
                $userPriceInfo->save();
            } else {
                Price::create(['user_id' => $uid, 'coin' => $payInfo->product_content, 'vip_level' => 0]);
            }
        } else {
            if ($userPriceInfo !== null) {
                $userPriceInfo->vip_level = $payInfo->product_content;
                $userPriceInfo->coin = $userPriceInfo->coin + self::vipCoin($payInfo->product_content);
                $userPriceInfo->vip_start_time = $order->created_at;
                $userPriceInfo->vip_end_time = self::vipEndTime($payInfo->product_content, $order->created_at);
                $userPriceInfo->save();
            } else {
                Price::create(['user_id' => $uid, 'coin' => self::vipCoin($payInfo->product_content), 'vip_level' => $payInfo->product_content, 'vip_start_time' => $order->created_at]);
            }
        }

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
            //加三天
            case 4:
                return Carbon::parse($vip_start_time)->addDays(3)->toDateTimeString();
        }
    }
}
