<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace'=>'App\Http\Controllers\Api'], function () {
    $exceptCreateAndEdit = ['except' => ['create', 'edit']];
    Route::resource('categories', 'CategoryController', $exceptCreateAndEdit);
    Route::resource('genres', 'GenreController', $exceptCreateAndEdit);
    Route::resource('caster_member', 'CastMemberController', $exceptCreateAndEdit);
    Route::resource('video', 'VideoController', $exceptCreateAndEdit);
});
