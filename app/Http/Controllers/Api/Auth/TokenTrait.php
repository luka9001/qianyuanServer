<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2018/12/14
 * Time: 12:49 PM
 */

namespace App\Http\Controllers\Api\Auth;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

trait TokenTrait {
	public function issueToken( Request $request, $mobile, $password, $grantType, $scope = "" ) {

		$params = [
			'grant_type'    => $grantType,
			'client_id'     => '1',
			'client_secret' => '5xju5ChjG0L5uVV5uPcEqJEotejLFY6U78Z4pHye',
			'username'      => $mobile,
			'password'      => $password,
			'scope'         => $scope
		];

		$request->request->add( $params );

		$proxy = Request::create( 'oauth/token', 'POST' );

		return Route::dispatch( $proxy );
	}

	public function refreshToken( Request $request, $refresh_token, $scope = '' ) {
		$params = [
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refresh_token,
			'client_id'     => '1',
			'client_secret' => '5xju5ChjG0L5uVV5uPcEqJEotejLFY6U78Z4pHye',
			'scope'         => $scope
		];
		$request->request->add( $params );

		$proxy = Request::create( 'oauth/token', 'POST' );

		return Route::dispatch( $proxy );
	}
}