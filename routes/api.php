<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post(UserController::API_URL_LOGIN, [UserController::class, UserController::METHOD_LOGIN]);
Route::post(UserController::API_URL_REGISTER, [UserController::class, UserController::METHOD_REGISTER]);
Route::post(UserController::API_URL_SEND_CODE_TO, [UserController::class, UserController::METHOD_SEND_CODE_TO]);
Route::post(UserController::API_URL_VERIFY_CODE, [UserController::class, UserController::METHOD_VERIFY_CODE]);
