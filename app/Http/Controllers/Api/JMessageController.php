<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use JMessage\IM\Group;
use JMessage\JMessage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class JMessageController extends Controller {
	public function postJMessageStatus( Request $request ) {
		$this->validate( $request, [
			'status' => 'required|numeric',
		] );

		$status = request( 'status' );
		if ( $status == 1 ) {
			$request->user()->j_register_status = $status;
			$request->user()->save();

			return response()->json( array( 'code' => 200 ) );
		}

		return response()->json( array( 'code' => 201 ) );
	}

	public function groupAvatar( Request $request ) {
		$groupId = request( 'groupId' );
		if ( $groupId === '41156304' ) {
			$data['nickname'] = '西班牙';
			$data['photo']    = '/img/spanish_group.png';

			return response()->json( array( 'code' => 200, 'data' => $data ) );
		}
	}

	public function groupAvatarImg( $groupId ) {
		return Redirect::to( '/img/' . $groupId . '.png' );
	}

	public function img( $id ) {
		$user = User::find( $id );
		if ( isset( $user ) ) {
			$photo = json_decode( $user->lifephoto );
			return Redirect::to($photo[0]);
		}
	}


	public function addGroupMember( Request $request ) {
		$userName     = 'qy_' . $request->user()->id;
		$appKey       = '27837b1c1fed6927c288e3df';
		$masterSecret = 'c7664b0d3f55056db560ecab';

		$client    = new JMessage( $appKey, $masterSecret, [ 'disable_ssl' => true ] );
		$group     = new Group( $client );
		$gid       = 41156304; //西班牙群
		$usernames = [ $userName ];
		$response  = $group->addMembers( $gid, $usernames );
		Log::info($usernames);
		return response()->json( array( 'code' => 200 ,'data'=>print_r($response,1)) );
	}

	public function getMembersInfo( Request $request ) {
		$array     = request( 'array' );
		$userArray = array();
		foreach ( $array as $key => $value ) {
			$user = User::find( $value );
			if ( $user != null ) {
				array_push( $userArray, [
					'black_uid' => $user['id'],
					'name'      => $user['name'],
					'sex'       => $user['sex'],
					'live'      => $user['live'],
					'lifephoto' => $user['lifephoto']
				] );
			}
		}

		return response()->json( array( 'code' => 200, 'data' => $userArray ) );
	}
}
