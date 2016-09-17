<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/fb', 'FacebookController@login');
Route::get('/fb/callback', 'FacebookController@callback');

// FACEBOOK
// Login
Route::post('/fb/login', 'FacebookController@storeUserData');
// Pages
Route::get('/fb/{userId}/page/get', 'FacebookController@getPages');
Route::post('/fb/{userId}/page/{pageId}/post', 'FacebookController@createPost');
Route::get('/fb/{userId}/page/{pageId}', 'FacebookController@getPosts');
// Route::get('/fb/{userId}/page/{pageId}/insight', 'FacebookController@getInsight');

Route::get('/bl/getPageInfo/{keyword}', 'BukalapakController@getPages');
Route::get('/bl/postProduct/{productid}', 'BukalapakController@postProduct');

Route::get('/tokped/getPageInfo/{keyword}', 'TokopediaController@getPages');
Route::get('/tokped/postProduct/{productid}', 'TokopediaController@postProduct');
