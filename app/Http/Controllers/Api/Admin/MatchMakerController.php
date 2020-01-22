<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MatchMaker;
use App\Models\MatchMakerLog;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchMakerController extends Controller
{
    /**
     * 鍒嗛〉鏌ヨ鎵�鏈夌孩濞樻眰鍔╄褰�
     * @param Request $request
     * @return JsonResponse
     */
    public function getMatchMakerPage(Request $request)
    {
        $status = request('status');
        $data = MatchMaker::where('status', $status)->leftJoin('users', 'users.id', '=', 'matchmaker.uid')->select('matchmaker.id', 'matchmaker.uid', 'matchmaker.status', 'matchmaker.question', 'matchmaker.detail', 'matchmaker.wanted_uid', 'matchmaker.created_at', 'users.sex', 'users.name', 'users.live', 'users.lifephoto', 'users.mobile')->orderBy('id', 'desc')->paginate(10);
        return response()->json(array('code' => 200, 'data' => $data));
    }

    /**
     * 鏌ヨ绾㈠瀵硅瘽鍒嗛〉璁板綍
     * @param Request $request
     * @return JsonResponse
     */
    public function getAdminMatchMakerLogPage(Request $request)
    {
        $mid = request('mid');
        $uid = request('uid');
        $wanted_uid = request('wanted_uid');
        $status = request('status');
        //姹傚姪浜轰俊鎭�
        $user = MatchMaker::where([['matchmaker.id', $mid]])->leftJoin('users', 'users.id', '=', 'matchmaker.uid')->select('users.name', 'users.sex', 'users.birthdate', 'users.mobile', 'users.live', 'users.height', 'users.marrystatus', 'users.birthplace', 'users.language', 'users.education', 'users.lifephoto', 'matchmaker.question', 'matchmaker.detail', 'matchmaker.status as matchmakerstatus')->first();
        //姹傚姪瀵硅薄
        $wantedUser = User::find($wanted_uid);
        //娌熼�氳褰�
        $matchMakerLog = MatchMakerLog::where('mid', $mid)->orderBy('id', 'desc')->paginate(10);
        return response()->json(array('code' => 200, 'data' => $matchMakerLog, 'user' => $user, 'wanteduser' => $wantedUser));
    }

    /**
     * 淇濆瓨绾㈠瀵硅瘽
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
            'type' => 1,
        ]);
        return response()->json(array('code' => 200));
    }

    /**
     * 缁撴潫涓�娆＄孩濞樻彺鍔�
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
