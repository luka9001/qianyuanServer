<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/1
 * Time: 2:50 PM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favoriteme;
use App\Models\Favorites;
use App\Models\MatchMaker;
use App\User;
use Illuminate\Http\Request;

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
     */
    public function getMembers()
    {
        $members = User::where('state', '!=', 0)->orderBy('id', 'desc')->paginate(10);
        return response()->json(array('code' => 200, 'data' => $members));
    }

    public function getMembersLogin(Request $request)
    {
        $members = User::where('id', '!=', $request->user()->id)->where('state', '!=', 0)->orderBy('id', 'desc')->paginate(10);
        return response()->json(array('code' => 200, 'data' => $members));
    }

    public function memberDetail(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|string|max:2',
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
            'id' => 'required|string|max:2',
        ]);
        $id = request('id');
        $user = User::find($id);
        unset($user['remember_token']);
        unset($user['password']);
        unset($user['email']);
        $favoriteMeUid = $user->favoriteme()->where('who_f_me_uid', $request->user()->id)->first();
        $user->favoriteMeUid = isset($favoriteMeUid->who_f_me_uid);
        return response()->json(array('code' => 200, 'data' => $user));
    }

    public function saveFavorites(Request $request)
    {
        $this->validate($request, [
            'favorite_uid' => 'required|numeric',
        ]);
        $favorite = new Favorites();
        $favorite->favorite_uid = request('favorite_uid');
        //我关注谁
        User::find($request->user()->id)->favorites()->save($favorite);

        //给被关注的用户添加谁关注我记录
        $favoriteme = new Favoriteme();
        $favoriteme->who_f_me_uid = $request->user()->id;
        User::find(request('favorite_uid'))->favoriteme()->save($favoriteme);
        return response()->json(array('code' => 200));
    }

    //数据先做软删除，曾今关注过的人，数据分析
    public function delFavorites(Request $request)
    {
        $this->validate($request, [
            'favorite_uid' => 'required|numeric',
        ]);
        $favorite_uid = request('favorite_uid');
        $favorite = Favorites::where('user_id', $request->user()->id)->where('favorite_uid', $favorite_uid)->first();
        $status = $favorite->delete();

        $status_me = Favoriteme::where('user_id', $favorite_uid)->where('who_f_me_uid', $request->user()->id)->delete();
        if ($status && $status_me) {
            return response()->json(array('code' => 200, 'status' => $status));
        } else {
            return response()->json(array('code' => 404));
        }
    }

    //求助红娘
    public function callMatchMaker(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|string|max:2',
            'question' => 'required|digits:1',
            'detail' => 'nullable|string|max:20',
        ]);

        $result = MatchMaker::create([
            'wanted_uid' => request('id'),
            'question' => request('question'),
            'detail' => request('detail'),
        ]);

        if ($result) {
            return response()->json(array('code' => 200));
        } else {
            return response()->json(array('code' => 201));
        }
    }
}
