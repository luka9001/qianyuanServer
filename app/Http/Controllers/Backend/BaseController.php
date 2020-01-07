<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2018/4/26
 * Time: 上午9:58
 */

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PermissionGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use \Config;
use \Response;
use \View;

class BaseController extends Controller
{

    // 错误码
    const STATUS_OK = 0; // 正常
    const STATUS_UNKNOWN_ERROR = 500; // 内部错误
    const STATUS_BAD_REQUEST = 101; // 错误的请求
    const STATUS_DUPLICATE_APPLICATIONS = 150; // 错误的请求
    const STATUS_AUTH_FAIL = 401; // APPKEY与APPSEC认证失败
    const STATUS_AUTH_EXPIRED = 1; // 授权过期
    const STATUS_NOT_FOUND = 404; // 页面不存在
    const STATUS_ACCOUNT_LOCKED = 600; // 账号被锁定
    const STATUS_EMPTY_DATA = 201; // 操作成功，空数据

    // 错误消息
    protected static $errorMsg = array(
        self::STATUS_OK => '成功',
        self::STATUS_UNKNOWN_ERROR => '服务器内部错误',
        self::STATUS_BAD_REQUEST => '错误的请求',
        self::STATUS_AUTH_FAIL => '认证失败',
        self::STATUS_AUTH_EXPIRED => '授权过期',
        self::STATUS_NOT_FOUND => '数据未找到',
        self::STATUS_EMPTY_DATA => '操作成功，数据不存在！',
        self::STATUS_ACCOUNT_LOCKED => '您的账号已被锁定',
    );

    //用户权限
    protected $userPermission;

    public function __construct()
    {

        $this->middleware('auth');
        header("Content-Type: text/html; charset=UTF-8");

        $this->middleware(function ($request, $next) {

            //权限
            if (!Request::is('*auth/log*')) {
                !is_null($this->userPermission) || $this->userPermission = $this->getPermissionGroup(Auth::user()->group_id);

                View::share('menus', $this->getMenu());
            }

            return $next($request);
        });
    }

    /**
     * 获取用户权限组
     *
     * @return array
     */
    protected function getPermissionGroup($group_id)
    {
        $permissions = array();
        //find
        $permissionGroup = PermissionGroup::find($group_id);
        if ($permissionGroup) {
            if ($permissionGroup->permissions != '*') {
                $permissions = unserialize($permissionGroup->permissions);
            } else {
                $permissions = '*';
            }
        }

        return $permissions;
    }

    protected function getMenu()
    {
        $menu = Config::get('BackendMenu');

        if ($this->userPermission == '*') {
            return $menu;
        }
        //非管理员权限，需要过滤菜单项
        foreach ($menu as $key => $val) {
            if (isset($val['permission'])) {
                if ($val['permission'] == '*') {
                    continue;
                } else {
                    if (count($val['submenu'])) {
                        foreach ($val['submenu'] as $sKey => $sVal) {

                            if (!in_array($sVal['permission'], $this->userPermission)) {
                                unset($menu[$key]['submenu'][$sKey]);
                            }
                        }
                        //如果子功能没有了，则删除父菜单
                        if (!count($menu[$key]['submenu'])) {
                            unset($menu[$key]);
                        }
                    }
                }
            }
        }
        return $menu;
    }

    /**
     * 返回错误
     *
     * @param string $error
     * @param integer $code
     *
     * @return object
     */
    public static function error($code, $error = '')
    {
        if (isset(self::$errorMsg[$code])) {
            $error = self::$errorMsg[$code] . (empty($error) ? '' : ": [$error]");
        }

        return Response::json([
            'succeed' => 0,
            'error_code' => $code,
            'error_desc' => $error,
        ]);
    }

    public function back($result, $succes = '', $fail = '')
    {
        if ($result) {
            return Redirect::back()->withMessage($succes);
        } else {
            return Redirect::back()->withMessage($fail)->withColor('danger')->withInput();
        }
    }
}
