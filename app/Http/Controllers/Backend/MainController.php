<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2018/5/21
 * Time: 下午2:50
 */

namespace App\Http\Controllers\Backend;

use App\User;
use Illuminate\Http\Request;

class MainController extends BaseController
{
    public function main(Request $request)
    {
        return view('Backend.main');
    }
}
