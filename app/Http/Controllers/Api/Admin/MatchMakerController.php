<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\MatchMaker;
use App\Models\MatchMakerLog;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MatchMakerController extends Controller
{
    /**
     * 分页查询所有红娘求助记录
     * @param Request $request
     * @return JsonResponse
     */
    public function getMatchMakerPage(Request $request)
    {
        $status = request('status');
        $data = MatchMaker::where('status', $status)->leftJoin('users', 'users.id', '=', 'matchmaker.uid')->select('matchmaker.id', 'matchmaker.uid', 'matchmaker.status', 'matchmaker.question', 'matchmaker.detail', 'matchmaker.wanted_uid', 'matchmaker.created_at', 'users.sex', 'users.name', 'users.live', 'users.lifephoto', 'users.mobile')->paginate(10);
        return response()->json(array('code' => 200, 'data' => $data));
    }

    /**
     * 查询红娘对话分页记录
     * @param Request $request
     * @return JsonResponse
     */
    public function getAdminMatchMakerLogPage(Request $request)
    {
        $mid = request('mid');
        $uid = request('uid');
        $wanted_uid = request('wanted_uid');
        $status = request('status');
        //求助人信息
        $user = MatchMaker::where([['matchmaker.id', $mid]])->leftJoin('users', 'users.id', '=', 'matchmaker.uid')->select('users.name', 'users.sex', 'users.birthdate', 'users.mobile', 'users.live', 'users.height', 'users.marrystatus', 'users.birthplace', 'users.language', 'users.education', 'users.lifephoto', 'matchmaker.question', 'matchmaker.detail', 'matchmaker.status as matchmakerstatus')->first();
        //求助对象
        $wantedUser = User::find($wanted_uid);
        //沟通记录
        $matchMakerLog = MatchMakerLog::where('mid', $mid)->orderBy('id', 'desc')->paginate(10);
        return response()->json(array('code' => 200, 'data' => $matchMakerLog, 'user' => $user, 'wanteduser' => $wantedUser));
    }

    /**
     * 保存红娘对话
     * @param Request $request
     * @return JsonResponse
     */
    public function postAdminMatchMakerLog(Request $request)
    {
        $currentUserId = $request->user()->id;
        $mid = request('mid');
        $content = request('content');
        MatchMakerLog::create([
            'uid' => $currentUserId,
            'mid' => $mid,
            'content' => $content,
            'type' => 1
        ]);
        return response()->json(array('code' => 200));
    }

    /**
     * 结束一次红娘援助
     * @param Request $request
     * @return JsonResponse
     */
    public function postAdminMatchMakerFinish(Request $request)
    {
        $mid = request('mid');
        $matchMaker = MatchMaker::find($mid);
        $matchMaker->status = 1;
        $matchMaker->save();
        return response()->json(array('code' => 200));
    }
}
