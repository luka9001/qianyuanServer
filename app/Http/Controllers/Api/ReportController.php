<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function report(Request $request)
    {
        $report_id = request('report_id');
        $type = request('type');
        $data = request('data');

        Report::create([
            'report_id' => $report_id,
            'type' => $type,
            'data' => $data,
        ]);

        return response()->json(array('code' => 200));
    }
}
