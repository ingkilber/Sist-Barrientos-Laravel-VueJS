<?php

// Localization
Route::get('/js/lang', function () {

    $lang = config('app.locale');
    $files = glob(resource_path('lang/' . $lang . '/*.php'));
    $strings = [];
    foreach ($files as $file) {
        $name = basename($file, '.php');
        if ($name !== "lang") {
            $new_keys = require $file;
            $strings = array_merge($strings, $new_keys);
        }
    }

    header('Content-Type: text/javascript');
    echo ('window.i18n = ' . json_encode(array("lang" => $strings)) . ';');
    exit();
})->name('assets.lang');