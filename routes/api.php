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
    

// Route::post('/alsb_encode_crypt', ['as' => 'lsb_encode_crypt', 'uses' => 'Api/StegnographyController@LSBEncodeCrypt']);
// Route::post('/alsb_decode_crypt', ['as' => 'lsb_decode_crypt', 'uses' => 'Api/StegnographyController@LSBDecodeCrypt']);


Route::get('/index','Api\StegnographyController@index');

//params = {encImage,msg,offset} , return {encImage:"base46IMAGE"}
Route::any('/imgEncrypt',  'Api\StegnographyController@ImageEncodeCrypt');

// params = {encImage} , return {text:"string"}
Route::any('/imgDecrypt',  'Api\StegnographyController@ImageDecodeCrypt');

// params = {encVideo,msg,offset} , return {encVideo:"Video.mp4"}
Route::any('/vidEncrypt',  'Api\StegnographyController@VideoEncodeCrypt');

// params = {encVideo} , return {text:"String"}
Route::any('/vidDecrypt',  'Api\StegnographyController@VideoDecodeCrypt');
