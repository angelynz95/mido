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

// INSTAGRAM
Route::post('/ig/login', 'InstagramController@storeUserData');
Route::get('/ig/{userId}/post/get', 'InstagramController@getPosts');
Route::post('/ig/{userId}/post/image', 'InstagramController@uploadPhoto');

// BUKALAPAK
Route::get('/bl/getPageInfo/{keyword}', 'BukalapakController@getPages');
Route::get('/bl/postProduct/{productid}', 'BukalapakController@postProduct');

// TOKOPEDIA
Route::get('/tokped/getPageInfo/{keyword}', 'TokopediaController@getPages');
Route::get('/tokped/postProduct/{productid}', 'TokopediaController@postProduct');

// NEWS
Route::get('/news/get', 'NewsController@getPages');

// SRIBULANCER
Route::post('/sl/get', 'SribulancerController@getPages');

// DEMAND
Route::get('/insight/test/{keyword}', 'DemandInsightController@test');
Route::get('/insight/getInfoBahan/{keyword}', 'DemandInsightController@getInfoBahan');
