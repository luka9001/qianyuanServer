<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchMaker;
use App\Models\MatchMakerLog;
use App\Models\Price;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatckMakerController extends Controller
{

    /**
     * 求助红娘
     *
     * @param Request $request
     * @return JsonResponse
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
            return response()->json(array(
                'code' => 200,
            ));
        } else {
            return response()->json(array(
                'code' => 201,
            ));
        }
    }

    /**
     * 查询求助红娘分页记录
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserMatchMakerPage(Request $request)
    {
        $currentUserId = $request->user()->id;
        $data = MatchMaker::where([
            [
                'uid',
                $currentUserId,
            ],
        ])->leftJoin('users', 'users.id', '=', 'matchmaker.wanted_uid')
            ->select('matchmaker.id', 'matchmaker.question', 'matchmaker.detail', 'matchmaker.wanted_uid', 'matchmaker.status', 'matchmaker.created_at', 'users.sex', 'users.name', 'users.live', 'users.lifephoto')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return response()->json(array(
            'code' => 200,
            'data' => $data,
        ));
    }

    /**
     * 保存红娘对话
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function postUserMatchMakerLog(Request $request)
    {
        $currentUserId = $request->user()->id;
        $mid = request('mid');
        $content = request('content');
        $type = request('type');
        $matchMaker = MatchMaker::find($mid);
        if ($matchMaker->status === 0) {
            MatchMakerLog::create([
                'uid' => $currentUserId,
                'mid' => $mid,
                'content' => $content,
                'type' => $type,
            ]);
            return response()->json(array(
                'code' => 200,
            ));
        } else {
            return response()->json(array('code' => 202, 'matchmakerstatus' => $matchMaker->status));
        }
    }

    /**
     * 查询红娘对话分页记录
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserMatchMakerLogPage(Request $request)
    {
        $currentUserId = $request->user()->id;
        $mid = request('mid');

        $matchMakerStatus = MatchMaker::find($mid)->status;
        $matchMakerLog = MatchMakerLog::where([
            [
                'mid',
                $mid,
            ],
            [
                'uid',
                $currentUserId,
            ],
        ])
            ->select('content', 'created_at', 'type')
            ->paginate(10);
        return response()->json(array(
            'code' => 200,
            'data' => $matchMakerLog,
            'matchmakerstatus' => $matchMakerStatus,
        ));
    }
}
