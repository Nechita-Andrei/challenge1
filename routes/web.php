<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['namespace' => 'App\Http\Controllers'], function()
{
    Route::get('/index', 'ImportController@index')->name('import.index');
    Route::post('/import','ImportController@import')->name('import');
    Route::get("/room/{roomnumber?}", 'RoomController@details')->name("room.details");
});
