<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class LocalizationController extends Controller
{
    public function getLanguageDirectory()
    {
        $dir = '../resources/lang';
        return array_diff(scandir($dir), array('..', '.', '.DS_Store'));
    }
}
