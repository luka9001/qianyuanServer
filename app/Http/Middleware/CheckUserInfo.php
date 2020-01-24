<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckUserInfo
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next( $request );
        if ( Auth::check() ) {
            $data = $response->getData();
            if(isset($data->code)){
                if($data->code === 200){
                    $user = Auth::user();
                    $userInfo = [];
                    $userInfo['check_status'] = $user['check_status'];
                    $userInfo['state'] = $user['state'];
                    $data->userinfo = $userInfo;
                    $response->setData($data);
                }
            }
        }

        return $response;
    }
}
