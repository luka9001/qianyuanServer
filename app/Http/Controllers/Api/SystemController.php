<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemInfo;
use Illuminate\Http\Request;

class SystemController extends Controller {
	public function getSysInfo(Request $request) {
		$info = SystemInfo::find( 1 );
		$data['android_version'] = $info->android_version;
		$data['ios_version'] = $info->ios_version;
		$data['j_register_status'] = $request->user()->j_register_status;
		$data['check_status'] = $request->user()->check_status;
		$data['check_detail'] = $request->user()->check_detail;
		return response()->json( array( 'code' => 200, 'version' => $data ) );
	}
}
