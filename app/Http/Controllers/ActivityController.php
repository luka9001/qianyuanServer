<?php

namespace App\Http\Controllers;

class ActivityController extends Controller
{
    public function activityView()
    {
        return view('em');
    }

    public function payPalClientView()
    {
        return view('paypalclient');
    }
}
