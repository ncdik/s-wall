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
    return view('index', [
    	'messages' => Message::orderBy('created_at', 'desc')->get(),
    	'usertok' => (Auth::user())?md5(Session::token().Auth::user()->id.Auth::user()->name):'',
    ]);
})->name('index');

Auth::routes();

Route::get('password/reset', function(){
	abort('404');
});

Route::post('/msg/create', 'MessageController@create');
Route::post('/msg/edit', 'MessageController@edit');
Route::post('/msg/delete', 'MessageController@delete');

Route::get('/home', 'HomeController@index')->name('home');
