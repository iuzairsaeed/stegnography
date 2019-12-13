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
Route::post('/encrypt',  'Api\StegnographyController@LSBEncodeCrypt')->middleware('cors');
Route::post('/decrypt',  'Api\StegnographyController@LSBDecodeCrypt')->middleware('cors');