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
});

Route::group(['middleware' => ['auth:api'], 'prefix' => 'v1'], function () {
    Route::post('/getmysocial', 'Api\SocialController@getMySocial');

    Route::post('/getmemberslogin', 'Api\MembersController@getMembersLogin');
    Route::post('/getmemberdetaillogin', 'Api\MembersController@memberDetailLogin');
    Route::post('/getmyfavorites', 'Api\MembersController@getMyFavorites');
    Route::post('/getfavoriteme', 'Api\MembersController@getFavoriteMe');

    Route::post('/upload', 'Api\Auth\UserController@fileUpload');
    Route::post('/realname', 'Api\Auth\UserController@realNameAuthentication');
    Route::post('/pi', 'Api\Auth\UserController@personalInformation');
    Route::get('/getuserinfo', 'Api\Auth\UserController@getUserInfo');
   
    Route::post('/savefavorites', 'Api\MembersController@saveFavorites');
    Route::post('/delfavorites', 'Api\MembersController@delFavorites');
    Route::post('/cmk', 'Api\MembersController@callMatchMaker');

    Route::post('/sendmessage', 'Api\SocialController@sendMessage');

    Route::post('/postlikes', 'Api\SocialController@postLike');
    Route::post('/postcomment', 'Api\SocialController@postComment');
    Route::post('/getcomments', 'Api\SocialController@getComments');
    Route::post('/getcommentsbyuser', 'Api\SocialController@getCommentsByUser');
    Route::post('/getthumbupbyuser', 'Api\SocialController@getThumbUpByUser');
    Route::post('/getcommentscount', 'Api\SocialController@getCommentsCount');
});

Route::post('/login', 'Api\Auth\UserController@login');
Route::post('/register', 'Api\Auth\UserController@register');
Route::post('/code', 'Api\Auth\UserController@emailCode');
Route::post('/refreshtoken', 'Api\Auth\UserController@refresh_token');

// Route::post('/test', 'TestController@test');
