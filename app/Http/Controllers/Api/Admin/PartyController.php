<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Utils\VipStatus;
use App\Models\Party;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PartyController extends Controller
{
    public $successStatus = 200;
    public $failStatus = 500;

    /**
     * 根据未审核、审核、不通过各种状态获取活动列表
     */
    public function GetWaitForCheck()
    {
        $status = request('status');
        $user = Party::where([['party.check_status', $status]])->leftJoin('users', 'users.id', '=', 'party.uid')->select('users.name', 'users.mobile', 'party.id', 'party.title', 'party.address')->orderBy('party.id', 'desc')->paginate(10);
        return response()->json(['success' => $user], $this->successStatus);
    }

    /**
     * 获取线下活动具体信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function partyDetail(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        $id = request('id');
        $party = Party::where('party.id',$id)->leftJoin('users','users.id','=','party.uid')->select('party.id','party.text','party.photos','party.type','party.created_at','party.male','party.female','party.check_status','party.check_detail','party.total','party.title','party.address','party.end_at','users.name','users.mobile','users.sex')->first();

//        $party = Party::find('id');
        return response()->json(['success' => $party], $this->successStatus);
    }

    /**
     * 提交审核结果
     */
    public function postCheckResult()
    {
        $id = request('id');
        $status = request('status');
        $detail = request('detail');
        $party = Party::find($id);
        $party->check_status = $status;
        if ($status === 2) {
            $party->check_detail = $detail;
        }
        $party->save();
        return response()->json(['code' => 200]);
    }
}
