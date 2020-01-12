<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Validator;

class UserController extends Controller
{
    //
    // use ProxyTrait;
    public $successStatus = 200;
    public $failStatus = 500;

    /**
     *  微信小程序注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function WxLogin(Request $request)
    {
        $admin = Admin::where('username', $request->input('username'))->first();
        if ($admin && password_verify($request->input('password'), $admin->password)) {
            \DB::table('oauth_access_tokens')->where('user_id', $admin->id)->where('name', 'admin')->update(['revoked' => 1]);
            $success['token'] = $admin->createToken('admin')->accessToken;
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    /**
     * 查看用户详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function WxDetails(Request $request)
    {
        // $user = Admin::where('id', $request->input('id'))->first();
        $user = $request->user()->username;
        return response()->json(['success' => $user], $this->successStatus);
    }
}
