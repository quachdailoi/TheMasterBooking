<?php

use App\Http\Controllers\CategoryController as CategoryC;
use App\Http\Controllers\FileController as FileC;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManagerController as ManagerC;
use App\Http\Controllers\UserController as UserC;
use App\Http\Controllers\ProductController as ProductC;
use App\Http\Controllers\ProductOrderController as ProductOrderC;
use App\Http\Controllers\ServiceCategoryController as ServiceCategoryC;
use App\Http\Controllers\ServiceController as ServiceC;
use App\Http\Controllers\ServiceOrderController as ServiceOrderC;
use App\Http\Controllers\StoreController as StoreC;
use App\Models\ServiceOrder;
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
Route::post(UserC::API_URL_REGISTER, [UserC::class, UserC::METHOD_REGISTER]);
Route::post(UserC::API_URL_SEND_CODE_TO, [UserC::class, UserC::METHOD_SEND_CODE_TO]);
Route::post(UserC::API_URL_RESET_PASSWORD, [UserC::class, UserC::METHOD_RESET_PASSWORD]);

Route::group(['middleware' => 'auth:api', 'prefix' => UserC::PREFIX], function () {
    Route::get(UserC::API_URL_LOGOUT, [UserC::class, UserC::METHOD_LOGOUT]);
    Route::post(UserC::API_URL_CHANGE_PASSWORD, [UserC::class, UserC::METHOD_CHANGE_PASSWORD]);
    Route::get(UserC::API_URL_GET_USER_PROFILE, [UserC::class, UserC::METHOD_GET_PROFILE]);
    Route::post(UserC::API_URL_UPDATE_USER_PROFILE, [UserC::class, UserC::METHOD_UPDATE_PROFILE]);
    Route::get(UserC::API_URL_GET_CART, [UserC::class, UserC::METHOD_GET_CART]);
    Route::post(UserC::API_URL_UPDATE_CART, [UserC::class, UserC::METHOD_UPDATE_CART]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => FileC::PREFIX], function () {
    Route::post(FileC::API_URL_UPLOAD_FILE_S3, [FileC::class, FileC::METHOD_UPLOAD_FILE_S3]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => CategoryC::PREFIX], function () {
    Route::get(CategoryC::API_URL_GET_ALL, [CategoryC::class, CategoryC::METHOD_GET_ALL]);
    Route::post(CategoryC::API_URL_CREATE_CATEGORY, [CategoryC::class, CategoryC::METHOD_CREATE]);
    Route::post(CategoryC::API_URL_UPDATE_CATEGORY, [CategoryC::class, CategoryC::METHOD_UPDATE]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => ProductC::PREFIX], function () {
    Route::post(ProductC::API_URL_GET_PRODUCTS, [ProductC::class, ProductC::METHOD_GET_PRODUCTS]);
    Route::get(ProductC::API_URL_ADD_TO_CART, [ProductC::class, ProductC::METHOD_ADD_TO_CART]);
    Route::delete(ProductC::API_URL_REMOVE_FROM_CART, [ProductC::class, ProductC::METHOD_REMOVE_FROM_CART]);
    Route::post(ProductC::API_URL_CREATE_PRODUCT, [ProductC::class, ProductC::METHOD_CREATE_PRODUCT]);
    Route::post(ProductC::API_URL_UPDATE_PRODUCT, [ProductC::class, ProductC::METHOD_UPDATE_PRODUCT]);
    Route::delete(ProductC::API_URL_DELETE_PRODUCT, [ProductC::class, ProductC::METHOD_DELETE_PRODUCT]);
    Route::get(ProductC::API_URL_GET_ALL, [ProductC::class, ProductC::METHOD_GET_ALL]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => ProductOrderC::PREFIX], function () {
    Route::post(ProductOrderC::API_URL_CHECKOUT, [ProductOrderC::class, ProductOrderC::METHOD_CHECKOUT]);
    Route::get(ProductOrderC::API_URL_GET_ORDER_DETAILS, [ProductOrderC::class, ProductOrderC::METHOD_GET_ORDER_DETAILS]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => StoreC::PREFIX], function () {
    Route::get(StoreC::API_URL_GET_STORES, [StoreC::class, StoreC::METHOD_GET_STORES]);
    Route::get(StoreC::API_URL_GET_STORE, [StoreC::class, StoreC::METHOD_GET_STORE]);
    Route::post(StoreC::API_URL_CREATE_STORE, [StoreC::class, StoreC::METHOD_CREATE_STORE]);
    Route::get(StoreC::API_URL_GET_CITIES_HAVE_STORE, [StoreC::class, StoreC::METHOD_GET_CITIES_HAVE_STORE]);
    Route::post(StoreC::API_URL_GET_STORE_BY_CITY, [StoreC::class, StoreC::METHOD_GET_STORE_BY_CITY]);
    Route::post(StoreC::API_URL_UPDATE_STORE, [StoreC::class, StoreC::METHOD_UPDATE_STORE]);
    Route::delete(StoreC::API_URL_DELETE_STORE, [StoreC::class, StoreC::METHOD_DELETE_STORE]);
    Route::post(StoreC::API_URL_UPDATE_WORK_SCHEDULE, [StoreC::class, StoreC::METHOD_UPDATE_WORK_SCHEDULE]);
    Route::get(StoreC::API_URL_GET_BOOKING_TIME, [StoreC::class, StoreC::METHOD_GET_BOOKING_TIME]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => ServiceC::PREFIX], function () {
    Route::post(ServiceC::API_URL_GET_SERVICES, [ServiceC::class, ServiceC::METHOD_GET_SERVICES]);
    Route::post(ServiceC::API_URL_CREATE_SERVICE, [ServiceC::class, ServiceC::METHOD_CREATE_SERVICE]);
    Route::get(ServiceC::API_URL_GET_ALL_SERVICES_WITH_CATEGORY, [ServiceC::class, ServiceC::METHOD_GET_ALL_SERVICES_WITH_CATEGORY]);
    Route::post(ServiceC::API_URL_UPDATE_SERVICE, [ServiceC::class, ServiceC::METHOD_UPDATE_SERVICE]);
    Route::delete(ServiceC::API_URL_DELETE_SERVICE, [ServiceC::class, ServiceC::METHOD_DELETE_SERVICE]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => ServiceCategoryC::PREFIX], function () {
    Route::get(ServiceCategoryC::API_URL_GET_CATEGORIES, [ServiceCategoryC::class, ServiceCategoryC::METHOD_GET_ALL]);
    Route::post(ServiceCategoryC::API_URL_CREATE, [ServiceCategoryC::class, ServiceCategoryC::METHOD_CREATE]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => HomeController::PREFIX], function () {
    Route::get(HomeController::API_URL_GET_DATA, [HomeController::class, HomeController::METHOD_GET_DATA]);
    Route::get(HomeController::API_URL_GET_ALL_CATEGORIES_AND_PRODUCTS, [HomeController::class, HomeController::METHOD_GET_ALL_CATEGORIES_AND_PRODUCTS]);
    Route::get(HomeController::API_URL_GET_ALL_CATEGORIES_AND_SERVICES, [HomeController::class, HomeController::METHOD_GET_ALL_CATEGORIES_AND_SERVICES]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => ServiceOrderC::PREFIX], function () {
    Route::post(ServiceOrderC::API_URL_ORDER, [ServiceOrderC::class, ServiceOrderC::METHOD_ORDER]);
    Route::get(ServiceOrderC::API_URL_GET_ORDER_DETAILS, [ServiceOrderC::class, ServiceOrderC::METHOD_GET_ORDER_DETAILS]);
});

Route::group(['middleware' => 'auth:api', 'prefix' => ManagerC::PREFIX], function () {
    Route::post(ManagerC::API_URL_CREATE_STAFF, [ManagerC::class, ManagerC::METHOD_CREATE_STAFF]);
    Route::get(ManagerC::API_URL_GET_ALL_STAFFS, [ManagerC::class, ManagerC::METHOD_GET_ALL_STAFFS]);
    Route::post(ManagerC::API_URL_UPDATE_STAFF, [ManagerC::class, ManagerC::METHOD_UPDATE_STAFF]);
    Route::delete(ManagerC::API_URL_DELETE_STAFF, [ManagerC::class, ManagerC::METHOD_DELETE_STAFF]);
    Route::get(ManagerC::API_URL_GET_STAFF_BY_ID, [ManagerC::class, ManagerC::METHOD_GET_STAFF_BY_ID]);

    Route::post(ManagerC::API_URL_CREATE_SHIFT, [ManagerC::class, ManagerC::METHOD_CREATE_SHIFT]);
    Route::get(ManagerC::API_URL_GET_ALL_SHIFTS, [ManagerC::class, ManagerC::METHOD_GET_ALL_SHIFTS]);
    Route::post(ManagerC::API_URL_UPDATE_SHIFT, [ManagerC::class, ManagerC::METHOD_UPDATE_SHIFT]);
    Route::delete(ManagerC::API_URL_DELETE_SHIFT, [ManagerC::class, ManagerC::METHOD_DELETE_SHIFT]);
});
