<?php

namespace App\Http\Controllers\Api;

use App\Models\ADSignUp;
use App\Models\Party;
use App\Models\PartySignUp;
use App\Models\Price;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class PartyController extends Controller
{
    /**
     * 聚会信息发布
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function publishParty(Request $request)
    {
        if ($request->user()->state !== 1) {
            return response()->json(array('code' => 201));
        }

        $this->validate($request, [
            'text' => 'required|string|max:200',
        ]);
        $user = $request->user();

        $id = request('id');
        $title = request('title');
        $text = request('text');
        $type = request('type');
        $total = request('total');
        $male = request('male');
        $female = request('female');
        $address = request('address');
        $end_at = request('end_at');
        $checkStatus = request('check_status');
        //表示第一次发布
        if ($checkStatus === '-1') {
            $party = new Party();
        } else {
            $party = Party::find($id);
        }
        $party['title'] = $title;
        $party['uid'] = $user->id;
        $party['address'] = $address;
        $party['end_at'] = $end_at;
        $party['text'] = $text;
        $party['type'] = $type;
        $party['total'] = $total;
        $party['male'] = $male;
        $party['female'] = $female;
        $party['check_status'] = 0;

        $fileStoragePath = public_path() . '/uploadFile/party/';
        $dateFolder = date('Y-m-d', time()); // 日期作为目录
        $folder = $fileStoragePath . $dateFolder;
        $files = $request->files;
        if (count($files) > 0) {
            if (!file_exists($folder) && !@mkdir($folder, 0775, true)) {
                return self::error(self::STATUS_BAD_REQUEST, '目录没有写入权限！');
            }
            $photos = array();

            foreach ($files as $file) {
                $clientName = $file->getClientOriginalName(); //初始名
                $entension = 'jpeg';
                $newName = $dateFolder . '/' . Uuid::uuid4() . '.' . $entension;
                $file->move($folder, $newName);
                array_push($photos, $newName);
            }

            $party['photos'] = json_encode($photos);
        }

        try {
            $party->save();
            return response()->json(array('code' => 200));
        } catch (Exception $e) {
            return response()->json(array('code' => 201));
        }
    }

    /**
     * 获取活动列表
     * @return JsonResponse
     */
    public function getPartyList()
    {
        try {
            $party = Party::where('check_status', 1)->orderByDesc('id')->paginate(10);
            return response()->json(array('code' => 200, 'data' => $party));
        } catch (Exception $e) {
            return response()->json(array('code' => 201));
        }
    }

    /**
     * 获取活动详情
     * @param $pid
     * @return JsonResponse
     */
    public function getPartyById($pid)
    {
        try {
            $party = Party::find($pid);
            $user = User::find($party->uid);
            $party->userPhoto = $user->lifephoto;
            $party->name = $user->name;
            $party->sex = $user->sex;
            $party->live = $user->live;
            $price = Price::where('user_id', $party->uid)->first();
            $party->vip_level = $price->vip_level;
            $sTotal = PartySignUp::where('pid', $pid)->count();
            $party->sTotal = $sTotal;
            $sMale = PartySignUp::where([['usex', 0], ['pid', $pid]])->count();
            $party->sMaleCount = $sMale;
            $sFemale = PartySignUp::where([['usex', 1], ['pid', $pid]])->count();
            $party->sFemaleCount = $sFemale;
            return response()->json(array('code' => 200, 'data' => $party));
        } catch (Exception $e) {
            Log::info($e);
            return response()->json(array('code' => 201));
        }
    }

    /**
     * 报名
     * @param Request $request
     * @return JsonResponse
     */
    public function partySignUp(Request $request)
    {
        $uid = $request->user()->id;
        $usex = $request->user()->sex;
        $pid = request('pid');
        $ptype = request('ptype');
        $ptotal = request('ptotal');
        $maleCount = request('pmale');
        $femaleCount = request('pfemale');
        //减少一次查询
//        $party = Party::find($pid);
        try {
            $checkCount = PartySignUp::where([['pid', $pid], ['uid', $uid]])->count();
            //该活动已报
            if ($checkCount > 0) {
                return response()->json(array('code' => 203));
            }
        } catch (Exception $e) {
            return response()->json(array('code' => 201));
        }

        //不限制人数
        if ($ptype === 1) {
            return $this->signUpUtil(1, 0, [
                'uid' => $uid,
                'pid' => $pid,
                'usex' => $usex
            ]);
        } //比较总人数
        else if ($ptype === 2) {
            $count = PartySignUp::where('pid', $pid)->count();
            return $this->signUpUtil($ptotal, $count, [
                'uid' => $uid,
                'pid' => $pid,
                'usex' => $usex
            ]);
        } //按男女比例
        else if ($ptype === 3) {
            $sexCount = PartySignUp::where([['pid', $pid], ['usex', $usex]])->count();
            //男性会员比较
            if ($usex === 0) {
                return $this->signUpUtil($maleCount, $sexCount, [
                    'uid' => $uid,
                    'pid' => $pid,
                    'usex' => $usex
                ]);
            } //女性会员比较
            else if ($usex === 1) {
                return $this->signUpUtil($femaleCount, $sexCount, [
                    'uid' => $uid,
                    'pid' => $pid,
                    'usex' => $usex
                ]);
            }
        }
    }

    /**
     * 判断是否能报名，第一个参数报名总数，第二个参数人数
     * @param $total
     * @param $signCount
     * @return JsonResponse
     */
    private function signUpUtil($total, $signCount, $params)
    {
        if ($total > $signCount) {
            try {
                PartySignUp::create($params);
                return response()->json(array('code' => 200));
            } catch (Exception $e) {
                return response()->json(array('code' => 201));
            }
        } else {
            //报名人数已满
            return response()->json(array('code' => 202));
        }
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getMySignUpParty(Request $request)
    {
        try {
            $data = PartySignUp::where('party_signup.uid', $request->user()->id)->leftJoin('party', 'party_signup.pid', '=', 'party.id')->orderByDesc('party_signup.id')->paginate(10);
            return response()->json(array('code' => 200, 'data' => $data));
        } catch (Exception $e) {
            Log::info($e);
            return response()->json(array('code' => 201));
        }
    }

    public function getMyPublishParty(Request $request)
    {
        try {
            $data = Party::where('uid', $request->user()->id)->orderByDesc('id')->paginate(10);
//            $data = PartySignUp::where('party_signup.uid', $request->user()->id)->leftJoin('party', 'party_signup.pid', '=', 'party.id')->paginate(10);
            return response()->json(array('code' => 200, 'data' => $data));
        } catch (Exception $e) {
            return response()->json(array('code' => 201));
        }
    }
}
