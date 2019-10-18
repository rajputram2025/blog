<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('createuser','ServiceController@createuser');

Route::get('userlist/{id}','ServiceController@userlist');

Route::post('userdetails','ServiceController@userdetails');

Route::post('updateuserdetails','ServiceController@updateuserdetails');

Route::post('deleteuser','ServiceController@deleteuser');

// create post

Route::post('createpost','ServiceController@createpost');

Route::post('editpost','ServiceController@editpost');

Route::post('postdetails','ServiceController@postdetails');

Route::post('postlist','ServiceController@postlist');

Route::post('postdelete','ServiceController@postdelete');