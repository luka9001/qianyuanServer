<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/1
 * Time: 2:50 PM
 */

namespace App\Http\Controllers\Api;

use App\Models\Favorites;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MembersController {
	public function getMembers( Request $request ) {
		//TODO 是否被当前用户标记
		DB::beginTransaction();
		$data = '';
		try {
			$members = User::where( 'id', '!=', $request->user()->id )->orderBy('id', 'desc')->paginate( 10 );
			//TODO 是否被当前用户标记
			foreach ( $members as $d ) {
				$count = Favorites::where( 'user_id', $request->user()->id )->where( 'favorite_uid', $d->id )->count();
				if ( $count > 0 ) {
					$d->isFavorite = 1;
				}
			}
			$data = $members;
		} catch ( \Exception $e ) {
			DB::rollBack();
		}
		DB::commit();

		return response()->json( array( 'code' => 200, 'data' => $data ) );
	}

	public function memberDetail(){
		$id = request('id');
		Log::info($id);
		$user = User::find($id);
		return response()->json(array('code'=>200,'data'=>$user));
}

	public function saveFavorites( Request $request ) {
		$favorite               = new Favorites();
		$favorite->favorite_uid = request( 'favorite_uid' );
		User::find( $request->user()->id )->favorites()->save( $favorite );

		return response()->json( array( 'code' => 200 ) );
	}

	//数据先做软删除，曾今关注过的人，数据分析
	public function delFavorites( Request $request ) {
		$favorite_uid = request( 'favorite_uid' );
		$favorite     = Favorites::where( 'user_id', $request->user()->id )->where( 'favorite_uid', $favorite_uid )->first();
		$status       = $favorite->delete();
		if ( $status ) {
			return response()->json( array( 'code' => 200, 'status' => $status ) );
		} else {
			return response()->json( array( 'code' => 404 ) );
		}
	}
}