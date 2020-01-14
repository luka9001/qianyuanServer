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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    $user = $request->user();
    $return = array();
    $return['nickname'] = $user['name'];
    $return['email'] = $user['email'];
    $return['state'] = $user['state'];
    $return['lifephoto'] = $user['lifephoto'];

    return $return;
});

Route::group(['prefix' => 'v1'], function () {
    Route::post('/getmembers', 'Api\MembersController@getMembers');
    Route::post('/getmemberdetail', 'Api\MembersController@memberDetail');

    Route::post('/getsocial', 'Api\SocialController@getSocial');
    Route::post('/getcomments', 'Api\SocialController@getComments');
});

Route::group(['middleware' => ['auth:api'], 'prefix' => 'v1'], function () {
    Route::post('/coin', 'Api\PriceController@getCoin');
    Route::post('/pfm', 'Api\PriceController@payForMessage');
    Route::post('/mkpi', 'Api\PriceController@matckMakerPriceInfo');
    Route::post('/cd', 'Api\PriceController@coinDiscount');
    Route::post('/vd', 'Api\PriceController@vipDisCount');
    Route::post('/cpi', 'Api\PriceController@getCoinPayInfo');
    Route::post('/vpi', 'Api\PriceController@getVipPayInfo');
    Route::post('/paypal', 'Api\PriceController@payPal');
});

Route::group(['middleware' => ['auth:api'], 'prefix' => 'v1'], function () {
    Route::post('/getmysocial', 'Api\SocialController@getMySocial');

    Route::post('/getmemberslogin', 'Api\MembersController@getMembersLogin');
    Route::post('/getmemberdetaillogin', 'Api\MembersController@memberDetailLogin');
    Route::post('/getmyfavorites', 'Api\MembersController@getMyFavorites');
    Route::post('/getfavoriteme', 'Api\MembersController@getFavoriteMe');
    Route::post('/pbl', 'Api\MembersController@postBlackList');
    Route::post('/dbl', 'Api\MembersController@delBlackList');
    Route::get('/gbl', 'Api\MembersController@getBlackList');

    Route::post('/upload', 'Api\Auth\UserController@fileUpload');
    Route::post('/realname', 'Api\Auth\UserController@realNameAuthentication');
    Route::post('/pi', 'Api\Auth\UserController@personalInformation');
    Route::post('/getuserinfo', 'Api\Auth\UserController@getUserInfo');
    Route::post('/pjs', 'Api\Auth\UserController@postJMessageStatus');

    Route::post('/savefavorites', 'Api\MembersController@saveFavorites');
    Route::post('/delfavorites', 'Api\MembersController@delFavorites');
    Route::post('/cmk', 'Api\MembersController@callMatchMaker');

    Route::post('/delsocial', 'Api\SocialController@delMySocial');
    Route::post('/sendmessage', 'Api\SocialController@sendMessage');
    Route::post('/postlikes', 'Api\SocialController@postLike');
    Route::post('/postcomment', 'Api\SocialController@postComment');
    Route::post('/gun', 'Api\SocialController@getUserInfoById');

    Route::post('/getcommentsbyuser', 'Api\SocialController@getCommentsByUser');
    Route::post('/getthumbupbyuser', 'Api\SocialController@getThumbUpByUser');
    Route::post('/getcommentscount', 'Api\SocialController@getCommentsCount');

    Route::post('/adsu', 'Api\ADController@activitySignUp');

    Route::post('/agm', 'Api\JMessageController@addGroupMember');
    Route::post('/gmi', 'Api\JMessageController@getMembersInfo');
    Route::post('/ga', 'Api\JMessageController@groupAvatar');
});

Route::group(['middleware' => ['auth:api'], 'prefix' => 'v1'], function () {
    Route::post('/report', 'Api\ReportController@report');
});

Route::post('/rp', 'Api\Auth\UserController@resetPassword');
Route::post('/login', 'Api\Auth\UserController@login');
Route::post('/register', 'Api\Auth\UserController@register');
Route::post('/code', 'Api\Auth\UserController@emailCode');
Route::post('/sc', 'Api\Auth\UserController@smsCode');
Route::post('/refreshtoken', 'Api\Auth\UserController@refresh_token');

//后台
Route::group(['prefix' => 'v1'], function () {
    Route::post('admin/register', 'Api\Admin\UserController@WxRegister');
    Route::post('auth/loginValidate', 'Api\Admin\UserController@WxLogin');

});

Route::group(['middleware' => 'auth:xcx', 'prefix' => 'v1'], function () {
    Route::post('admin/details', 'Api\Admin\UserController@WxDetails');
});
