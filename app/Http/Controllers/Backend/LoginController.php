<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2018/5/21
 * Time: 下午2:50
 */

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class LoginController extends Controller
{
    public function login()
    {
        return view('Backend.auth.loginAdmin');
    }

    public function postLogin()
    {
        $email = Input::get('email');
        $password = Input::get('password');
        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            // 认证通过...
            return redirect()->intended('main');
        } else {
            return redirect()->back()->withMessage('用户名或密码错误！')->withColor('danger');
        }
    }

    public function fusionLogin()
    {
        Auth::loginUsingId(Input::get('uid'));
        return redirect()->intended('admin/un-order');
    }

    /**
     * 注销登录
     *
     * @return Response
     */
    public function getLogout()
    {
        Auth::logout();

        return Redirect::to('login')->with('message', '你已经成功注销当前登录.');
    }

    /*
     * 游客注册
     */
    public function register()
    {
        // $data = Input::all();

        // $rules = Config::get('rules.youke-user-create');
        // //验证
        // $validator = Validator::make(Input::all(), rules . php);
        // if ($validator->fails()) {
        //     return Redirect::back()->withErrors($validator)->withInput();
        // }

        // $count = User::where('email', $data['email'])->count();

        // if ($count > 0) {
        //     return Redirect::back()->withErrors('账户名已存在！')->withInput();
        // }

        $user = AdminUser::create([
            'email' => 'admin',
            'password' => bcrypt('abcxxx123'),
        ]);
        Auth::login($user);

        // return redirect()->intended('admin/new-order');
        return '200';
    }

    public function registerview()
    {
        return view('Backend.auth.register');
    }
}
