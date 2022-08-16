<?php

$gainConfig = require_once '../config/gain.php';


if ($gainConfig['installed'] == false) {

    $sub_folder = str_replace('/index.php', '', $_SERVER['PHP_SELF']);

    $base_url = rtrim($sub_folder, '/');
    $base_url = $base_url ? $base_url.'/' : '/';
    $base_url = str_replace('/app/environment', '', $base_url);
    $base_url = str_replace('js/lang.js/', '', $base_url);

    $ignore = [
        //sub folder fix
        $base_url.'js/lang.js',
        $base_url.'index.php/app/environment/index.php',
        $base_url.'index.php/app/environment',
        $base_url.'index.php/app/environment/install',
        $base_url.'index.php/app/environment/install/index.php',
        $base_url.'index.php',
        $base_url.'app/environment/database',
        $base_url.'app/environment/admin',

        //root fix
        '/js/lang.js',
        '/index.php/app/environment/index.php',
        '/index.php/app/environment',
        '/index.php/app/environment/install',
        '/index.php/app/environment/install/index.php',
        '/index.php',
        '/index.php/app/environment/admin',
        '/index.php/app/environment/database'

    ];

    if (!in_array($_SERVER['PHP_SELF'], $ignore)) {
        echo '<script>window.location.url = "/install"</script>';
    }
}


define('LARAVEL_START', microtime(true));


require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';


$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();
$kernel->terminate($request, $response);




