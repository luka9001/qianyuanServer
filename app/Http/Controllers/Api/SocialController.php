<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/20
 * Time: 9:47 AM
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\SocialMessage;
use Illuminate\Http\Request;

class SocialController extends Controller {
	//发布状态消息
	public function sendMessage( Request $request ) {
		$user                     = $request->user();
		$socialMessage            = new SocialMessage();
		$socialMessage['message'] = request( 'message' );

		$fileStoragePath = public_path() . '/uploadFile/social/';
		$dateFolder      = date( 'Y-m-d', time() ); // 日期作为目录
		$folder          = $fileStoragePath . $dateFolder;
		$files           = $request->files;
		if ( count( $files ) > 0 ) {
			if ( ! file_exists( $folder ) && ! @mkdir( $folder, 0775, true ) ) {
				return self::error( self::STATUS_BAD_REQUEST, '目录没有写入权限！' );
			}
			$photos = array();

			foreach ( $files as $file ) {
				$clientName = $file->getClientOriginalName();//初始名
				$entension  = 'png';

				$user    = $request->user();
				$newName = $clientName . '.' . $entension;
				$file->move( $folder, $newName );
				array_push( $photos, $newName );
			}

			$socialMessage['photos'] = json_encode( $photos );
		}

		$status = $user->social_messages()->save( $socialMessage );
		if ( $status ) {
			return response()->json( array( 'code' => '200' ) );
		} else {
			return response()->json( array( 'code' => '201' ) );
		}
	}

	//获取社交圈信息
	public function getSocial( Request $request ) {
		$socialMessage = SocialMessage::where( 'social_message.id', '!=', $request->user()->id )->leftJoin( 'users', 'social_message.user_id', '=', 'users.id' )->select( 'social_message.id', 'social_message.user_id', 'social_message.message', 'social_message.liked', 'social_message.created_at', 'users.lifephoto', 'users.name' )->paginate( 10 );
		foreach ( $socialMessage as $item ) {
			$item['commentcount'] = Comment::where( 'social_message_id', '=', $item->id )->count();
		}

		return response()->json( array( 'code' => 200, 'data' => $socialMessage ) );
	}
}