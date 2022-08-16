<?php


namespace App\Setup\Manager;


use Illuminate\Support\Facades\Artisan;

class StorageManager
{
    public function link()
    {
        Artisan::call('storage:link');
    }

}
