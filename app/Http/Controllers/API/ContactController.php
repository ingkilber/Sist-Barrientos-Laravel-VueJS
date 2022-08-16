<?php

namespace App\Http\Controllers\API;

use App\Libraries\Permissions;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{

    public function permissionCheck()
    {
        return new Permissions;
    }


}