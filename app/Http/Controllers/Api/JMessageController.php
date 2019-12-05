<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use JMessage\IM\Group;
use JMessage\JMessage;

class JMessageController extends Controller
{
    public function groupAvatar(Request $request)
    {
        $groupId = request('groupId');
        if ($groupId === '41156304') {
            $data['nickname'] = '西班牙';
            $data['photo'] = '/img/spanish_group.png';
            return response()->json(array('code' => 200, 'data' => $data));
        }
    }

    public function addGroupMember(Request $request)
    {
        $userName = env('JMESSAGE_TAG') . $request->user()->id;
        $appKey = '27837b1c1fed6927c288e3df';
        $masterSecret = 'c7664b0d3f55056db560ecab';

        $client = new JMessage($appKey, $masterSecret, ['disable_ssl' => true]);
        $group = new Group($client);
        $gid = 41156304; //西班牙群
        $usernames = [$userName];
        $response = $group->addMembers($gid, $usernames);
        return response()->json(array('code' => 200));
    }

    public function getMembersInfo(Request $request)
    {
        $array = request('array');
        $userArray = array();
        foreach ($array as $key => $value) {
            $user = User::find($value);
            if ($user != null) {
                array_push($userArray, ['black_uid' => $user['id'], 'name' => $user['name'], 'sex' => $user['sex'], 'live' => $user['live'], 'lifephoto' => $user['lifephoto']]);
            }
        }
        return response()->json(array('code' => 200, 'data' => $userArray));
    }
}
