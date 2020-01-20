<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/1
 * Time: 2:50 PM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Utils\VipStatus;
use App\Models\ActivitiesAD;
use App\Models\BlackList;
use App\Models\Favorites;
use App\Models\MatchMaker;
use App\Models\PayForMessage;
use App\Models\Price;
use App\Models\PriceInfo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MembersController extends Controller
{
    // public function getMembers(Request $request)
    // {
    //     //TODO 是否被当前用户标记
    //     DB::beginTransaction();
    //     $data = '';
    //     try {
    //         $members = User::where('id', '!=', $request->user()->id)->where('state', '!=', 0)->orderBy('id', 'desc')->paginate(10);
    //         //TODO 是否被当前用户标记
    //         foreach ($members as $d) {
    //             $count = Favorites::where('user_id', $request->user()->id)->where('favorite_uid', $d->id)->count();
    //             if ($count > 0) {
    //                 $d->isFavorite = 1;
    //             }
    //         }
    //         $data = $members;
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //     }
    //     DB::commit();

    //     return response()->json(array('code' => 200, 'data' => $data));
    // }

    /**
     * state 为 0 的账户 连照片都没有 所以不返回
     * check_status 未审核用户不返回
     */
    public function getMembers(Request $request)
    {
        $this->validate($request, [
            'page' => 'required|numeric',
            'filter' => 'required|numeric',
        ]);

        $page = request('page');
        $filter = request('filter');
        $activitiesAD = ActivitiesAD::orderBy('id', 'desc')->get();
        if ($filter === 2) {
            $members = User::where([['state', '!=', 0], ['check_status', 1]])->orderBy('id', 'desc')->paginate(10);
        } else {
            $members = User::where([['state', '!=', 0], ['check_status', 1], ['sex', $filter]])->orderBy('id', 'desc')->paginate(10);
        }
        if (count($activitiesAD) >= $page) {
            return response()->json(array('code' => 200, 'data' => $members, 'ad' => $activitiesAD[$page - 1]));
        } else {
            return response()->json(array('code' => 200, 'data' => $members));
        }
    }

    /**
     * state 为 0 的账户 连照片都没有 所以不返回
     * check_status 未审核用户不返回
     */
    public function getMembersLogin(Request $request)
    {
        $this->validate($request, [
            'page' => 'required|numeric',
            'filter' => 'required|numeric',
        ]);

        $page = request('page');
        $filter = request('filter');
        $activitiesAD = ActivitiesAD::orderBy('id', 'desc')->get();
        if ($filter === 2) {
            $members = User::where([['state', '!=', 0], ['check_status', 1]])->orderBy('id', 'desc')->paginate(10);
        } else {
            $members = User::where([['state', '!=', 0], ['check_status', 1], ['sex', $filter]])->orderBy('id', 'desc')->paginate(10);
        }

        if (count($activitiesAD) >= $page) {
            return response()->json(array('code' => 200, 'data' => $members, 'ad' => $activitiesAD[$page - 1]));
        } else {
            return response()->json(array('code' => 200, 'data' => $members));
        }
    }

    public function memberDetail(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        $id = request('id');
        $user = User::find($id);
        unset($user['remember_token']);
        unset($user['password']);

        return response()->json(array('code' => 200, 'data' => $user));
    }

    public function memberDetailLogin(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        $id = request('id');
        $user = User::find($id);
        unset($user['remember_token']);
        unset($user['password']);
        unset($user['email']);
        unset($user['check_detail']);
        // $favoriteMeUid = $user->favoriteme()->where('who_f_me_uid', $request->user()->id)->first();
        // $user->favoriteMeUid = isset($favoriteMeUid->who_f_me_uid);
        $favoriteMeUid = $request->user()->favorites()->where('favorite_uid', $id)->first();
        $user->favoriteMeUid = isset($favoriteMeUid->favorite_uid);

        $blackListUid = $request->user()->blacklist()->where('black_uid', $id)->first();
        $user->blackListUid = isset($blackListUid->black_uid);

        $price = Price::where('user_id', $request->user()->id)->first();
        if ($price != null) {

            $user['vip_level'] = VipStatus::isVipNow($price->vip_end_time) ? $price->vip_level : 0;
            $user['coin'] = $price->coin;
            $user['vip_start_time'] = $price->vip_start_time;
        }

        $priceInfo = PriceInfo::where('type', 1)->first();
        $user['payCoin'] = $priceInfo->coin;

        //TODO::男性会员解锁聊天
        if ($request->user()->sex === 0) {
            $pCount = PayForMessage::where([['user_id', $request->user()->id], ['message_uid', $id]])->count();
            if ($pCount > 0) {
                $user['par_for_message'] = true;
            } else {
                $user['par_for_message'] = false;
            }
        }

        return response()->json(array('code' => 200, 'data' => $user));
    }

    public function saveFavorites(Request $request)
    {
        if ($request->user()->state != 1) {
            return response()->json(array('code' => 201));
        }

        $this->validate($request, [
            'favorite_uid' => 'required|numeric',
        ]);
        $favorite = new Favorites();
        $favorite->favorite_uid = request('favorite_uid');
        //我关注谁
        User::find($request->user()->id)->favorites()->save($favorite);

        /**
         * 首次关注用户心动币+1
         */
        $fCount = DB::table('favorites')->where('user_id', $request->user()->id)->count();
        if ($fCount === 1) {
            $price = Price::where('user_id', $request->user()->id)->first();
            $price->coin = $price->coin + 1;
            $price->save();
        }

        //给被关注的用户添加谁关注我记录
        // $favoriteme = new Favoriteme();
        // $favoriteme->who_f_me_uid = $request->user()->id;
        // User::find(request('favorite_uid'))->favoriteme()->save($favoriteme);
        return response()->json(array('code' => 200));
    }

    //数据先做软删除，曾今关注过的人，数据分析
    public function delFavorites(Request $request)
    {
        if ($request->user()->state != 1) {
            return response()->json(array('code' => 201));
        }

        $this->validate($request, [
            'favorite_uid' => 'required|numeric',
        ]);
        $favorite_uid = request('favorite_uid');
        $favorite = Favorites::where('user_id', $request->user()->id)->where('favorite_uid', $favorite_uid)->first();
        $status = $favorite->delete();

        // $status_me = Favoriteme::where('user_id', $favorite_uid)->where('who_f_me_uid', $request->user()->id)->delete();
        if ($status) {
            return response()->json(array('code' => 200, 'status' => $status));
        } else {
            return response()->json(array('code' => 404));
        }
    }

    public function getMyFavorites(Request $request)
    {
        DB::beginTransaction();
        $data = array();
        try {
            $favorite_ids = $request->user()->favorites()->orderBy('id', 'desc')->paginate(10);
            foreach ($favorite_ids as $key => $value) {
                $user = User::find($value->favorite_uid);
                array_push($data, ['id' => $user['id'], 'name' => $user['name'], 'sex' => $user['sex'], 'live' => $user['live'], 'lifephoto' => $user['lifephoto']]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('code' => 201));
        }
        DB::commit();

        return response()->json(array('code' => 200, 'data' => $data));
    }

    public function getFavoriteMe(Request $request)
    {
        DB::beginTransaction();
        $data = array();
        try {
            // $favorite_ids = $request->user()->favoriteme()->orderBy('id', 'desc')->paginate(10);
            // foreach ($favorite_ids as $key => $value) {
            //     $user = User::find($value->who_f_me_uid);
            //     array_push($data, ['id' => $user['id'], 'name' => $user['name'], 'sex' => $user['sex'], 'live' => $user['live'], 'lifephoto' => $user['lifephoto']]);
            // }

            $favorite_ids = Favorites::where('favorite_uid', $request->user()->id)->orderBy('id', 'desc')->paginate(10);
            foreach ($favorite_ids as $key => $value) {
                $user = User::find($value->user_id);
                array_push($data, ['id' => $user['id'], 'name' => $user['name'], 'sex' => $user['sex'], 'live' => $user['live'], 'lifephoto' => $user['lifephoto']]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('code' => 201));
        }
        DB::commit();

        return response()->json(array('code' => 200, 'data' => $data));
    }

    public function postBlackList(Request $request)
    {
        if ($request->user()->state != 1) {
            return response()->json(array('code' => 201));
        }

        $this->validate($request, [
            'id' => 'required|numeric',
        ]);

        $blackList = new BlackList();
        $blackList->black_uid = request('id');
        //拉黑了谁
        $request->user()->blacklist()->save($blackList);

        return response()->json(array('code' => 200));
    }

    public function delBlackList(Request $request)
    {
        if ($request->user()->state != 1) {
            return response()->json(array('code' => 201));
        }

        $this->validate($request, [
            'id' => 'required|numeric',
        ]);

        $request->user()->blacklist()->where('black_uid', request('id'))->delete();

        return response()->json(array('code' => 200));
    }

    public function getBlackList(Request $request)
    {
        if ($request->user()->state != 1) {
            return response()->json(array('code' => 201));
        }

        DB::beginTransaction();
        $data = array();
        try {
            $favorite_ids = $request->user()->blacklist()->orderBy('id', 'desc')->paginate(10);
            foreach ($favorite_ids as $key => $value) {
                $user = User::find($value->black_uid);
                array_push($data, ['black_uid' => $user['id'], 'name' => $user['name'], 'sex' => $user['sex'], 'live' => $user['live'], 'lifephoto' => $user['lifephoto']]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('code' => 201));
        }
        DB::commit();

        return response()->json(array('code' => 200, 'data' => $data));
    }
}
