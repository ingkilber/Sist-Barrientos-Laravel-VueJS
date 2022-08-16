<?php


namespace App\Http\Controllers;


use Illuminate\Support\Facades\Artisan;

class InstallDemoDataController
{
    /**
     * @return bool
     */
    public function run()
    {
        if (env('INSTALL_DEMO_DATA')) {
            $this->setMemoryLimit('500M');
            $this->setExecutionTime(500);

            Artisan::call('clear-compiled');
            Artisan::call('view:clear');

            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            Artisan::call('migrate:fresh --force');
            Artisan::call('db:demo');

            Artisan::call('storage:link');
            Artisan::call('queue:restart');
        }

        return 1;
    }

    /**
     * @param string $size
     */
    public function setMemoryLimit($size = '256M')
    {
        ini_set('memory_limit', $size);
    }

    /**
     * @param int $time
     */
    public function setExecutionTime($time = 300)
    {
        set_time_limit($time);
    }
}