<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function homePage()
    {
        $installCheck = config('gain.installed');
        if ($installCheck == true) {
            return view('auth.login');
        } else {
            return redirect('/install');
        }
    }
}