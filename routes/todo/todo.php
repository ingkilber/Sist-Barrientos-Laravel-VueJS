<?php
//product module
use App\Http\Controllers\API\TodoListController;

Route::group(['prefix' => 'todo'], function () {
    Route::post('store', [TodoListController::class, 'store']);
    Route::post('update-status', [TodoListController::class, 'upDateStatus']);
    Route::post('delete', [TodoListController::class, 'deleteData']);
    Route::post('set-due-date', [TodoListController::class, 'setDueDate']);
    Route::post('list', [TodoListController::class, 'getTodoData']);
});