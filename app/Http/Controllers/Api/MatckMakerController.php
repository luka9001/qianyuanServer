<?php

namespace App\Http\Controllers\Api;

use App\Models\MatchMaker;
use App\Models\MatchMakerLog;
use App\Models\Price;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MatckMakerController extends Controller
{
    /**
     * 求助红娘
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callMatchMaker(Request $request)
    {
        $currentUserId = $request->user()->id;
        $this->validate($request, [
            'id' => 'required|numeric',
            'question' => 'required|digits:1',
            'detail' => 'nullable|string|max:20',
        ]);

        $result = MatchMaker::create([
            'uid' => $currentUserId,
            'wanted_uid' => request('id'),
            'question' => request('question'),
            'detail' => request('detail'),
        ]);

        $price = Price::where('user_id', $currentUserId)->first();
        $price->coin = $price->coin - request('payCoin');
        $price->save();

        if ($result) {
            return response()->json(array('code' => 200));
        } else {
            return response()->json(array('code' => 201));
        }
    }

    /**
     * 求助红娘记录
     */
    public function getUserMatchMakerInfo(Request $request)
    {
        $currentUserId = $request->user()->id;
        $data = MatchMaker::where('uid', $currentUserId)->leftJoin('users', 'users.id', '=', 'matchmaker.wanted_uid')->select('matchmaker.id', 'matchmaker.question', 'matchmaker.detail', 'matchmaker.wanted_uid', 'matchmaker.created_at', 'users.sex', 'users.name', 'users.live', 'users.lifephoto')->paginate(10);
        return response()->json(array('code' => 200, 'data' => $data));
    }

    /**
     * 红娘对话
     */
    public function postUserMatchMakerLog(Request $request)
    {
        $currentUserId = $request->user()->id;
        $id = request('id');
        $matchMakerLog = MatchMakerLog::where([['mid', $id], ['uid', $currentUserId]])->paginate(10);
        return response()->json(array('code' => 200, 'data' => $matchMakerLog));
    }
}
