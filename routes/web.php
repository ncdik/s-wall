<?php

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
use App\Message;

Route::get('/', function () {
    return view('index', ['messages' => Message::orderBy('created_at', 'desc')->get()]);
})->name('index');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
