<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/update-earn', [App\Http\Controllers\TelegramController::class, 'updateEarns']);
Route::get('/user-info/{user_id}', [App\Http\Controllers\TelegramController::class, 'userInfo']);
Route::get('/referred-users/{userId}', [App\Http\Controllers\TelegramController::class, 'getReferredUsers']);


Route::get('/get-tasks', [App\Http\Controllers\TelegramController::class, 'getTasks']);
Route::get('/create-task', [App\Http\Controllers\TelegramController::class, 'createTask']);
Route::get('/delete-task/{id}', [App\Http\Controllers\TelegramController::class, 'deleteTask']);
