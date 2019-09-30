<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2018/12/14
 * Time: 3:06 PM
 */

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Laravel\Passport\Client;

class UserController extends Controller
{

    use TokenTrait;
    use StarSignTrait;

    private $client;

    public function __construct()
    {
        $this->client = Client::find(1);
    }

    public function emailCode(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users,email',
        ]);

        $email = request('email');

        $code = Redis::get($email);
        if (isset($code)) {
            return response()->json(array('code' => '201', 'msg' => '上次发送的验证码未过期,有效期5分钟,注意超时临界线！'));
        } else {
            $code = rand(0, 9999);

            Redis::setex($email, 3000, $code); //保留五分钟
            // Mail::send()的返回值为空，所以可以其他方法进行判断
            Mail::send('mail', ['code' => $code], function ($message) {
                $email = request('email');
                $to = $email;
                $message->to($to)->subject('牵缘————婚恋交友平台！');
            });

            return response()->json(array('code' => '200'));
        }
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'nickname' => 'required|max:10|unique:users,name',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|max:20',
            'code' => 'required',
        ]);

        $email = request('email');
        $password = request('password');
        $code = Redis::get($email);
        if ($code == null || $code != request('code')) {
            return response()->json(array('code' => '201', 'msg' => '验证码错误！或者过期！'));
        }

        $result = User::create([
            'name' => request('nickname'),
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $return = $this->issueToken($request, $email, $password, 'password', '*');

        return $return;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6|max:20',
        ]);

        $email = request('email');
        $loginTag = $email . 'login';
        $password = request('password');

        $loginTimes = Redis::get($loginTag);
        $saveTime = 3000;

        if (isset($loginTimes)) {
            if ($loginTimes > 4) {
                return response()->json(array('code' => '201', 'msg' => '不要再试啦！用户名密码错误,已超过5次,账户暂时锁定'));
            } else {
                $return = $this->issueToken($request, $email, $password, 'password', '*');
                if (!strpos($return, 'access_token')) {
                    $loginTimes = $loginTimes + 1;
                    Redis::setex($loginTag, $saveTime, $loginTimes);
                    return response()->json(array('code' => '201', 'msg' => '用户名密码错误,错误次数' . $loginTimes . '次,5次将暂时锁定账户'));
                } else {
                    return $return;
                }
            }
        } else {
            $return = $this->issueToken($request, $email, $password, 'password', '*');
            if (!strpos($return, 'access_token')) {
                Redis::setex($loginTag, $saveTime, 1);
                return response()->json(array('code' => '201', 'msg' => '用户名密码错误,错误次数1次,5次将暂时锁定账户'));
            } else {
                return $return;
            }
        }
    }

    public function refresh_token(Request $request)
    {
        $this->validate($request, [
            'refresh_token' => 'required',
        ]);

        $refresh_token = request('refresh_token');
        $return = $this->refreshToken($request, $refresh_token, '*');

        return $return;
    }

    //已废弃
    public function realNameAuthentication(Request $request)
    {
        $realname = request('realname');
        $birthdate = request('birthdate');
        $sex = request('sex');
        $birthplace = request('birthplace');
        $height = request('height');
        $education = request('education');
        $hobby = request('hobby');
        $personality = request('personality');
        $job = request('job');
        $live = request('live');

        $user = $request->user();
        $user['realname'] = $realname;
        $user['birthdate'] = date("Y-m-d H:i:s", strtotime($birthdate));
        $user['starsign'] = $this->get_constellation(strtotime($birthdate));
        $user['sex'] = $sex == 'false' ? '0' : '1';
        $user['birthplace'] = $birthplace;
        $user['height'] = $height;
        $user['education'] = $education;
        $user['hobby'] = ltrim(rtrim($hobby, '}'), '{');
        $user['personality'] = ltrim(rtrim($personality, '}'), '{');
        $user['job'] = $job;
        $user['live'] = $live;
        $user['phone'] = request('phone');
        $user['state'] = 1;

        $fileStoragePath = public_path() . '/uploadFile/files/';
        $dateFolder = date('Y-m-d', time()); // 日期作为目录
        $folder = $fileStoragePath . $dateFolder;
        $files = $request->files;
        if (count($files) > 0) {
            if (!file_exists($folder) && !@mkdir($folder, 0775, true)) {
                return self::error(self::STATUS_BAD_REQUEST, '目录没有写入权限！');
            }
            foreach ($files as $file) {
                $clientName = $file->getClientOriginalName(); //初始名
                $entension = 'png';

                $user = $request->user();
                $newName = $clientName . $user->email . '.' . $entension;
                $file->move($folder, $newName);
                $src = '/uploadFile/files/' . $dateFolder . '/' . $newName;
                switch ($clientName) {
                    case 'lifephoto':
                        {
                            $user['lifephoto'] = $src;
                            break;
                        }
                    case 'greencard':
                        {
                            $user['greencard'] = $src;
                            break;
                        }
                    case 'divorce':
                        {
                            $user['divorce'] = $src;
                            $user['ismarred'] = 1;
                            break;
                        }
                }
            }

            $user->save();
            return response()->json(array('code' => '200'));
        } else {
            return response()->json(array('code' => '201', 'msg' => '未检测到文件！'));
        }
    }

    public function getUserInfo(Request $request)
    {
        $user = $request->user();
        unset($user['remember_token']);
        unset($user['password']);
        unset($user['email']);
        return response()->json(array('code' => 200, 'data' => $user));
    }

    public function personalInformation(Request $request)
    {
        // $this->validate($request, [
        //     // 'realname' => 'max:6',
        //     'birthDay' => 'required|date',
        //     'sex' => 'required|string|max:2',
        //     'birthplace' => 'required|string|max:10',
        //     'live' => 'required|string|max:10',
        //     'height' => 'required|string|max:3',
        //     'marryStatus' => 'required|string|max:2',
        //     'language' => 'required|string',
        //     'school' => 'nullable|string|max:2',
        //     'detail' => 'nullable|string|max:100',
        //     'smoke' => 'nullable|string|max:2',
        //     'drink' => 'nullable|string|max:2',
        //     'baby' => 'nullable|string|max:2',
        //     'income' => 'nullable|string|max:2',
        //     'car' => 'nullable|string|max:2',
        //     'house' => 'nullable|string|max:2',
        //     'occupation' => 'nullable|string|max:10',
        //     'religion' => 'nullable|string|max:2',
        //     'hobby' => 'nullable|string|max:10',

        //     'nbirthDay' => 'nullable|date',
        //     'nsex' => 'nullable|boolean',
        //     'nbirthplace' => 'nullable|string|max:10',
        //     'nlive' => 'nullable|string|max:10',
        //     'nheight' => 'nullable|string|max:3',
        //     'nmaxheight' => 'nullable|string|max:2',
        //     'nmarryStatus' => 'nullable|string|max:2',
        //     'nlanguage' => 'nullable|string|max:2',
        //     'nschool' => 'nullable|string|max:2',
        //     'nsmoke' => 'nullable|string|max:2',
        //     'ndrink' => 'nullable|string|max:2',
        //     'nbaby' => 'nullable|string|max:2',
        //     'nincome' => 'nullable|string|max:2',
        //     'ncar' => 'nullable|string|max:2',
        //     'nhouse' => 'nullable|string|max:2',
        //     'noccupation' => 'nullable|string|max:10',
        //     'nreligion' => 'nullable|string|max:2',
        // ]);

        // $realname = request('realname');
        $birthdate = request('birthDay');
        $sex = request('sex');
        $birthplace = request('birthplace');
        $height = request('height');
        $education = request('school');
        $live = request('live');
        $marrystatus = request('marryStatus');
        $detail = request('detail');
        $smoke = request('smoke');
        $drink = request('drink');
        $baby = request('baby');
        $income = request('income');
        $car = request('car');
        $house = request('house');
        $language = request('language');
        $occupation = request('occupation');
        $religion = request('religion');
        $hobby = request('hobby');

        //对象信息
        $nbirthdate = request('nbirthDay');
        $nsex = request('nsex');
        $nbirthplace = request('nbirthplace');
        $nheight = request('nheight');
        $nmaxheight = request('nmaxheight');
        $neducation = request('nschool');
        $nlive = request('nlive');
        $nmarrystatus = request('nmarryStatus');
        $nsmoke = request('nsmoke');
        $ndrink = request('ndrink');
        $nbaby = request('nbaby');
        $nincome = request('nincome');
        $ncar = request('ncar');
        $nhouse = request('nhouse');
        $nlanguage = request('nlanguage');
        $noccupation = request('noccupation');
        $nreligion = request('nreligion');

        $user = $request->user();
        // $user['realname'] = $realname;
        if ($birthdate != null) {
            $user['birthdate'] = date("Y-m-d", strtotime($birthdate));
            $user['starsign'] = $this->get_constellation(strtotime($birthdate));
        }
        $user['sex'] = $sex === '男' ? '0' : '1';
        $user['birthplace'] = $birthplace;
        $user['height'] = $height;
        $user['education'] = $education;
        $user['live'] = $live;
        // $user['phone'] = request('phone');
        $user['state'] = 1;
        $user['marrystatus'] = $marrystatus;
        $user['detail'] = $detail;
        $user['smoke'] = $smoke;
        $user['drink'] = $drink;
        $user['baby'] = $baby;
        $user['income'] = $income;
        $user['car'] = $car;
        $user['house'] = $house;
        $user['language'] = $language;
        $user['occupation'] = $occupation;
        $user['religion'] = $religion;
        $user['hobby'] = $hobby;

        //对象信息存储
        $user['nbirthdate'] = $nbirthdate == '' ? null : date("Y-m-d", strtotime($nbirthdate));
        $user['nsex'] = $nsex === '男' ? '0' : '1';
        $user['nbirthplace'] = $nbirthplace;
        $user['nheight'] = $nheight;
        $user['nmaxheight'] = $nmaxheight;
        $user['neducation'] = $neducation;
        $user['nlive'] = $nlive;
        $user['nmarrystatus'] = $nmarrystatus;
        $user['nsmoke'] = $nsmoke;
        $user['ndrink'] = $ndrink;
        $user['nbaby'] = $nbaby;
        $user['nincome'] = $nincome;
        $user['ncar'] = $ncar;
        $user['nhouse'] = $nhouse;
        $user['nlanguage'] = $nlanguage;
        $user['noccupation'] = $noccupation;
        $user['nreligion'] = $nreligion;

        $fileStoragePath = public_path() . '/uploadFile/files/';
        $dateFolder = date('Y-m-d', time()); // 日期作为目录
        $folder = $fileStoragePath . $dateFolder;
        $files = $request->files;
        $photo = array();
        if (count($files) > 0) {
            if (!file_exists($folder) && !@mkdir($folder, 0775, true)) {
                return self::error(self::STATUS_BAD_REQUEST, '目录没有写入权限！');
            }
            foreach ($files as $file) {
                $clientName = $file->getClientOriginalName(); //初始名
                $entension = 'png';

                $user = $request->user();
                $newName = $clientName . $user->email . '.' . $entension;
                $file->move($folder, $newName);
                $src = '/uploadFile/files/' . $dateFolder . '/' . $newName;
                array_push($photo, $src);
            }
            $user['lifephoto'] = json_encode($photo);
            $user->save();

            return response()->json(array('code' => '200'));
        } else {
            return response()->json(array('code' => '201', 'msg' => '未检测到文件！'));
        }
    }

    public function fileUpload(Request $request)
    {
        $fileStoragePath = public_path() . '/uploadFile/files/';
        $dateFolder = date('Y-m-d', time()); // 日期作为目录
        $folder = $fileStoragePath . $dateFolder;
        $files = $request->files;
        Log::info(count($files));
        if (count($files) > 0) {

            if (!file_exists($folder) && !@mkdir($folder, 0775, true)) {
                return self::error(self::STATUS_BAD_REQUEST, '目录没有写入权限！');
            }

            foreach ($files as $file) {
                $clientName = $file->getClientOriginalName(); //初始名
                $entension = 'png';

                $user = $request->user();
                $newName = $clientName . $user->email . '.' . $entension;
                $file->move($folder, $newName);
                $src = '/uploadFile/files/' . $dateFolder . '/' . $newName;
            }

            // $file = $request->file('file');
            // Log::info($file);
            // $clientName = $file->getClientOriginalName(); //初始名
            // $entension = $file->getClientOriginalExtension();

            // $fileTypes = array(
            //     'jpeg',
            //     'jpg',
            //     'png',
            //     'gif',
            //     'bmp',
            // ); //图片文件类型
            // $isInFileType = in_array($entension, $fileTypes); //文件是否合格

            // if (!$isInFileType) {

            //     $error_message = array(
            //         'code' => 1,
            //         'msg' => '请上传PNG、JPEG、JPG、GIF、BMP格式的图片！',
            //     );
            //     $error_message = json_encode($error_message, 320);

            //     return $error_message;
            // }

            // $user = $request->user();
            // $newName = $clientName . $user->email . '.' . $entension;
            // $file->move($folder, $newName);
            // $src = '/uploadFile/files/' . $dateFolder . '/' . $newName;

            $error_message = array(
                'code' => 0,
                'msg' => '上传成功',
                'data' => array('href' => $src, 'filename' => $newName),
            );
            $error_message = json_encode($error_message, 320);

            return $error_message;
        } else {
            $error_message = array('code' => 1, 'msg' => '未检测到文件！');
            $error_message = json_encode($error_message, 320);

            return $error_message;
        }
    }
}
