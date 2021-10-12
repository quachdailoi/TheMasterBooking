<?php

use App\Http\Controllers\CategoryController as CategoryC;
use App\Http\Controllers\FileController as FileC;
use App\Http\Controllers\UserController as UserC;
use App\Http\Controllers\ProductController as ProductC;
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

Route::post(UserC::API_URL_LOGIN, [UserC::class, UserC::METHOD_LOGIN]);
Route::post(UserC::API_URL_REGISTER, [UserCr::class, UserC::METHOD_REGISTER]);
Route::post(UserC::API_URL_SEND_CODE_TO, [UserC::class, UserC::METHOD_SEND_CODE_TO]);
Route::post(UserC::API_URL_RESET_PASSWORD, [UserC::class, UserC::METHOD_RESET_PASSWORD]);

Route::group(['middleware' => 'auth:api', 'prefix' => UserC::PREFIX], function () {
    Route::get(UserC::API_URL_LOGOUT, [UserC::class, UserC::METHOD_LOGOUT]);
    Route::post(UserC::API_URL_CHANGE_PASSWORD, [UserC::class, UserC::METHOD_CHANGE_PASSWORD]);
    Route::get(UserC::API_URL_GET_USER_PROFILE, [UserC::class, UserC::METHOD_GET_PROFILE]);
    Route::post(UserC::API_URL_UPDATE_USER_PROFILE, [UserC::class, UserC::METHOD_UPDATE_PROFILE]);
    Route::get(UserC::API_URL_GET_CART, [UserC::class, UserC::METHOD_GET_CART]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => FileC::PREFIX], function () {
    Route::post(FileC::API_URL_UPLOAD_FILE_S3, [FileC::class, FileC::METHOD_UPLOAD_FILE_S3]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => CategoryC::PREFIX], function () {
    Route::post(CategoryC::API_URL_GET_CATEGORY_BY_STORE_ID, [CategoryC::class, CategoryC::METHOD_GET_CATEGORY_BY_STORE_ID]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => ProductC::PREFIX], function () {
    Route::post(ProductC::API_URL_GET_PRODUCTS, [ProductC::class, ProductC::METHOD_GET_PRODUCTS]);
    Route::post(ProductC::API_URL_ADD_TO_CART, [ProductC::class, ProductC::METHOD_ADD_TO_CART]);
    Route::post(ProductC::API_URL_REMOVE_FROM_CART, [ProductC::class, ProductC::METHOD_REMOVE_FROM_CART]);
    Route::post(ProductC::API_URL_CREATE_PRODUCT, [ProductC::class, ProductC::METHOD_CREATE_PRODUCT]);
    Route::post(ProductC::API_URL_UPDATE_PRODUCT, [ProductC::class, ProductC::METHOD_UPDATE_PRODUCT]);
});
