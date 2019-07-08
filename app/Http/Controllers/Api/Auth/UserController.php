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
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Laravel\Passport\Client;

class UserController extends Controller {

	use TokenTrait;
	use StarSignTrait;

	private $client;

	public function __construct() {
		$this->client = Client::find( 1 );
	}

	public function emailCode( Request $request ) {
		$this->validate( $request, [
			'email' => 'required|email|unique:users,email',
		] );

		$email = request( 'email' );
		$code  = rand( 0, 9999 );
		Redis::setex( $email, 300, $code );//保留五分钟
		// Mail::send()的返回值为空，所以可以其他方法进行判断
		Mail::send( 'mail', [ 'code' => $code ], function ( $message ) {
			$email = request( 'email' );
			$to    = $email;
			$message->to( $to )->subject( '牵缘————婚恋交友平台！' );
		} );

		return response()->json( array( 'code' => '200' ) );
	}

	public function register( Request $request ) {
		$this->validate( $request, [
			'nickname' => 'required',
			'email'    => 'required|email|unique:users,email',
			'password' => 'required|min:6',
			'code'     => 'required'
		] );

		$email    = request( 'email' );
		$password = request( 'password' );
		$code     = Redis::get( $email );

		if ( $code == null || $code != request( 'code' ) ) {
			return response()->json( array( 'code' => '201', 'msg' => '验证码错误！' ) );
		}

		$result = User::create( [
			'name'     => request( 'nickname' ),
			'email'    => $email,
			'password' => bcrypt( $password ),
		] );

		$return = $this->issueToken( $request, $email, $password, 'password', '*' );

		return $return;
	}

	public function login( Request $request ) {
		$this->validate( $request, [
			'email'    => 'required',
			'password' => 'required'
		] );

		$email    = request( 'email' );
		$password = request( 'password' );

		$return = $this->issueToken( $request, $email, $password, 'password', '*' );

		return $return;
	}

	public function refresh_token( Request $request ) {
		$this->validate( $request, [
			'refresh_token' => 'required',
		] );

		$refresh_token = request( 'refresh_token' );
		$return        = $this->refreshToken( $request, $refresh_token, '*' );

		return $return;
	}

	public function realNameAuthentication( Request $request ) {
		$realname    = request( 'realname' );
		$birthdate   = request( 'birthdate' );
		$sex         = request( 'sex' );
		$birthplace  = request( 'birthplace' );
		$height      = request( 'height' );
		$education   = request( 'education' );
		$hobby       = request( 'hobby' );
		$personality = request( 'personality' );
		$job         = request( 'job' );
		$live        = request( 'live' );

		$user                = $request->user();
		$user['realname']    = $realname;
		$user['birthdate']   = date( "Y-m-d H:i:s", strtotime( $birthdate ) );
		$user['starsign']    = $this->get_constellation( strtotime( $birthdate ) );
		$user['sex']         = $sex == 'false' ? '0' : '1';
		$user['birthplace']  = $birthplace;
		$user['height']      = $height;
		$user['education']   = $education;
		$user['hobby']       = ltrim( rtrim( $hobby, '}' ), '{' );
		$user['personality'] = ltrim( rtrim( $personality, '}' ), '{' );
		$user['job']         = $job;
		$user['live']        = $live;
		$user['phone']       = request( 'phone' );
		$user['state']       = 1;

		$fileStoragePath = public_path() . '/uploadFile/files/';
		$dateFolder      = date( 'Y-m-d', time() ); // 日期作为目录
		$folder          = $fileStoragePath . $dateFolder;
		$files           = $request->files;
		if ( count( $files ) > 0 ) {
			if ( ! file_exists( $folder ) && ! @mkdir( $folder, 0775, true ) ) {
				return self::error( self::STATUS_BAD_REQUEST, '目录没有写入权限！' );
			}
			foreach ( $files as $file ) {
				$clientName = $file->getClientOriginalName();//初始名
				$entension  = 'png';

				$user    = $request->user();
				$newName = $clientName . $user->email . '.' . $entension;
				$file->move( $folder, $newName );
				$src = '/uploadFile/files/' . $dateFolder . '/' . $newName;
				switch ( $clientName ) {
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
							$user['divorce']  = $src;
							$user['ismarred'] = 1;
							break;
						}
				}
			}

			$user->save();

			return response()->json( array( 'code' => '200' ) );
		} else {
			return response()->json( array( 'code' => '201', 'msg' => '未检测到文件！' ) );
		}
	}

	public function fileUpload( Request $request ) {
		$fileStoragePath = public_path() . '/uploadFile/files/';
		$dateFolder      = date( 'Y-m-d', time() ); // 日期作为目录
		$folder          = $fileStoragePath . $dateFolder;
		$files           = $request->files;
		Log::info( count( $files ) );
		if ( count( $files ) > 0 ) {

			if ( ! file_exists( $folder ) && ! @mkdir( $folder, 0775, true ) ) {
				return self::error( self::STATUS_BAD_REQUEST, '目录没有写入权限！' );
			}

			$file = $request->file( 'file' );
			Log::info( $file );
			$clientName = $file->getClientOriginalName();//初始名
			$entension  = $file->getClientOriginalExtension();

			$fileTypes    = array(
				'jpeg',
				'jpg',
				'png',
				'gif',
				'bmp'
			);//图片文件类型
			$isInFileType = in_array( $entension, $fileTypes );//文件是否合格

			if ( ! $isInFileType ) {

				$error_message = array(
					'code' => 1,
					'msg'  => '请上传PNG、JPEG、JPG、GIF、BMP格式的图片！'
				);
				$error_message = json_encode( $error_message, 320 );

				return $error_message;
			}


			$user    = $request->user();
			$newName = $clientName . $user->email . '.' . $entension;
			$file->move( $folder, $newName );
			$src = '/uploadFile/files/' . $dateFolder . '/' . $newName;

			$error_message = array(
				'code' => 0,
				'msg'  => '上传成功',
				'data' => array( 'href' => $src, 'filename' => $newName )
			);
			$error_message = json_encode( $error_message, 320 );

			return $error_message;
		} else {
			$error_message = array( 'code' => 1, 'msg' => '未检测到文件！' );
			$error_message = json_encode( $error_message, 320 );

			return $error_message;
		}
	}
}