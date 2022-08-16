<?php


// Cronjob
use App\Http\Controllers\API\EmailTemplateController;
use App\Http\Controllers\InstallDemoDataController;
use App\Http\Controllers\Setup\EnvironmentController;

Route::get('/corn-job', [EmailTemplateController::class, 'callCornJob']);

//Install demo data
Route::any('install-demo-data', [InstallDemoDataController::class, 'run'])
    ->name('install-demo-data');

Route::group(['prefix' => 'app'], function () {
    Route::get('environment', [EnvironmentController::class, 'index'])
        ->name('app.environment');

    Route::post('environment/database', [EnvironmentController::class, 'saveEnvironment'])
        ->name('app.environment.database');

    Route::get('environment/admin', [EnvironmentController::class, 'admin'])
        ->name('app.environment.admin');

    Route::post('environment/install', [EnvironmentController::class, 'store'])
        ->name('app.installer');
});
