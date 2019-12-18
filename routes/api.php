<?php

use Illuminate\Http\Request;

/*
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/index','Api\StegnographyController@index');

Route::any('/imgEncrypt',  'Api\StegnographyController@ImageEncodeCrypt');
//params = {encImage,msg,offset} , return {encImage:"base46IMAGE"}

// params = {encImage} , return {text:"string"}
Route::any('/imgDecrypt',  'Api\StegnographyController@ImageDecodeCrypt');

// params = {encVideo,msg,offset} , return {encVideo:"Video.mp4"}
Route::any('/vidEncrypt',  'Api\StegnographyController@VideoEncodeCrypt');

// params = {encVideo} , return {text:"String"}
Route::any('/vidDecrypt',  'Api\StegnographyController@VideoDecodeCrypt');
