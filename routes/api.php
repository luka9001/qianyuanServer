<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    $user = $request->user();
//    $return = array();
//    $return['nickname'] = $user['name'];
//    $return['email'] = $user['email'];
//    $return['state'] = $user['state'];
//    $return['lifephoto'] = $user['lifephoto'];
//
//    return $return;
//});

/**
 * 对游客公开的会员信息，朋友圈信息
 */
Route::group( [ 'prefix' => 'v1' ], function () {
	Route::post( '/getmembers', 'Api\MembersController@getMembers' );
	Route::post( '/getmemberdetail', 'Api\MembersController@memberDetail' );

	Route::post( '/getsocial', 'Api\SocialController@getSocial' );
	Route::post( '/getcomments', 'Api\SocialController@getComments' );
} );

/**
 * 代币及会员支付
 */
Route::group( [ 'middleware' => [ 'auth:api' ], 'prefix' => 'v1' ], function () {
	Route::post( '/coin', 'Api\PriceController@getCoin' );
	Route::post( '/pfm', 'Api\PriceController@payForMessage' );
	Route::post( '/mkpi', 'Api\PriceController@matckMakerPriceInfo' );
	Route::post( '/cd', 'Api\PriceController@coinDiscount' );
	Route::post( '/vd', 'Api\PriceController@vipDisCount' );
	Route::post( '/cpi', 'Api\PriceController@getCoinPayInfo' );
	Route::post( '/vpi', 'Api\PriceController@getVipPayInfo' );
	Route::post( '/paypal', 'Api\PriceController@payPal' );
} );

/**
 * 会员个人信息管理
 */
Route::group( [ 'middleware' => [ 'auth:api' ], 'prefix' => 'v1' ], function () {
	Route::post( '/upload', 'Api\Auth\UserController@fileUpload' );
	Route::post( '/realname', 'Api\Auth\UserController@realNameAuthentication' );
	Route::post( '/pi', 'Api\Auth\UserController@personalInformation' );
	Route::post( '/getuserinfo', 'Api\Auth\UserController@getUserInfo' );
	Route::post( '/pjs', 'Api\Auth\UserController@postJMessageStatus' );
} );

/**
 * 活动报名
 */
Route::group( [ 'middleware' => [ 'auth:api' ], 'prefix' => 'v1' ], function () {
	Route::post( '/adsu', 'Api\ADController@activitySignUp' );
} );


/**
 * 极光api
 */
Route::group( [ 'middleware' => [ 'auth:api' ], 'prefix' => 'v1' ], function () {
	Route::post( '/agm', 'Api\JMessageController@addGroupMember' );
	Route::post( '/gmi', 'Api\JMessageController@getMembersInfo' );
	Route::post( '/ga', 'Api\JMessageController@groupAvatar' );
} );

/**
 * 会员间流转信息,app第一个页面
 * 获取会员详细信息，收藏等
 */
Route::group( [ 'middleware' => [ 'auth:api' ], 'prefix' => 'v1' ], function () {
	Route::post( '/getmemberslogin', 'Api\MembersController@getMembersLogin' );
	Route::post( '/getmemberdetaillogin', 'Api\MembersController@memberDetailLogin' );
	Route::post( '/getmyfavorites', 'Api\MembersController@getMyFavorites' );
	Route::post( '/getfavoriteme', 'Api\MembersController@getFavoriteMe' );
	Route::post( '/pbl', 'Api\MembersController@postBlackList' );
	Route::post( '/dbl', 'Api\MembersController@delBlackList' );
	Route::get( '/gbl', 'Api\MembersController@getBlackList' );

	Route::post( '/savefavorites', 'Api\MembersController@saveFavorites' );
	Route::post( '/delfavorites', 'Api\MembersController@delFavorites' );

} );

/**
 * 朋友圈
 */
Route::group( [ 'middleware' => [ 'auth:api' ], 'prefix' => 'v1' ], function () {
	Route::post( '/getsociallogin', 'Api\SocialController@getSocialLogin' );
	Route::post( '/getmysocial', 'Api\SocialController@getMySocial' );

	Route::post( '/delsocial', 'Api\SocialController@delMySocial' );
	Route::post( '/sendmessage', 'Api\SocialController@sendMessage' );
	Route::post( '/getcommentslogin', 'Api\SocialController@getCommentsLogin' );
	Route::post( '/postlikes', 'Api\SocialController@postLike' );
	Route::post( '/postcomment', 'Api\SocialController@postComment' );
	Route::post( '/delmycomment', 'Api\SocialController@delMyComment' );
	Route::post( '/gun', 'Api\SocialController@getUserInfoById' );

	Route::post( '/getcommentsbyuser', 'Api\SocialController@getCommentsByUser' );
	Route::post( '/getthumbupbyuser', 'Api\SocialController@getThumbUpByUser' );
	Route::post( '/getcommentscount', 'Api\SocialController@getCommentsCount' );
} );

Route::group( [ 'middleware' => [ 'auth:api' ], 'prefix' => 'v1' ], function () {
	Route::post( '/report', 'Api\ReportController@report' );
} );

/**
 * 系统信息
 */
Route::group( [ 'middleware' => [ 'auth:api' ], 'prefix' => 'v1' ], function () {
	Route::get( '/getsi', 'Api\SystemController@getSysInfo' );
} );

Route::group( ['prefix' => 'v1'], function () {
	Route::get( '/getversion', 'Api\SystemController@getVersion' );
} );

/**
 * 会员登录
 */
Route::group( [], function () {
	Route::post( '/rp', 'Api\Auth\UserController@resetPassword' );
	Route::post( '/login', 'Api\Auth\UserController@login' );
	Route::post( '/register', 'Api\Auth\UserController@register' );
	Route::post( '/code', 'Api\Auth\UserController@emailCode' );
	Route::post( '/sc', 'Api\Auth\UserController@smsCode' );
	Route::post( '/refreshtoken', 'Api\Auth\UserController@refresh_token' );
} );

/**
 * 红娘功能
 */
Route::group( [ 'middleware' => [ 'auth:api' ], 'prefix' => 'v1' ], function () {
	Route::post( '/cmk', 'Api\MatckMakerController@callMatchMaker' );
	Route::get( '/mkp', 'Api\MatckMakerController@getUserMatchMakerPage' );
	Route::post( '/mkl', 'Api\MatckMakerController@postUserMatchMakerLog' );
	Route::get( '/mklp', 'Api\MatckMakerController@getUserMatchMakerLogPage' );
} );


/**
 * 后台登录
 */
Route::group( [ 'prefix' => 'v1' ], function () {
	Route::post( 'admin/register', 'Api\Admin\UserController@WxRegister' );
	Route::post( 'auth/loginValidate', 'Api\Admin\UserController@WxLogin' );
} );

/**
 * 后台资料审核
 */
Route::group( [ 'middleware' => [ 'auth:xcx' ], 'prefix' => 'v1/admin' ], function () {
	Route::post( 'details', 'Api\Admin\UserController@WxDetails' );
	Route::get( 'wfc', 'Api\Admin\UserController@GetWaitForCheck' );
	Route::post( 'userinfo', 'Api\Admin\UserController@memberDetail' );
	Route::post( 'checkresult', 'Api\Admin\UserController@postCheckResult' );
	Route::post( 'guv', 'Api\Admin\UserController@postGiveUserVip' );
} );

/**
 * 后台红娘求助
 */
Route::group( [ 'middleware' => [ 'auth:xcx' ], 'prefix' => 'v1/admin' ], function () {
	Route::get( 'mmp', 'Api\Admin\MatchMakerController@getMatchMakerPage' );
	Route::get( 'mmlp', 'Api\Admin\MatchMakerController@getAdminMatchMakerLogPage' );
	Route::post( 'mml', 'Api\Admin\MatchMakerController@postAdminMatchMakerLog' );
	Route::post( 'mmf', 'Api\Admin\MatchMakerController@postAdminMatchMakerFinish' );
} );

