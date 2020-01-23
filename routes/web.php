<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Illuminate\Support\Facades\Route as Route;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

Route::group(['prefix' => 'auth'], function () use ($router) {
    // User Authentication
    $router->post('/user/register', 'Auth\\UserAuthController@register');
    $router->post('/user/login', 'Auth\\UserAuthController@login');
    $router->post('/user/logout', 'Auth\\UserAuthController@logout');

    // Petugas Authentication
    $router->post('/petugas/register', 'Auth\\PetugasAuthController@register');
    $router->post('/petugas/login', 'Auth\\PetugasAuthController@login');
    $router->post('/petugas/logout', 'Auth\\PetugasAuthController@logout');
});

// Authorized Routes
Route::group(['middleware' => ['auth']], function ($router) {
    // Petugas End-Point
    $router->get('/petugas', 'PetugasController@index');
    $router->get('/petugas/{id}', 'PetugasController@show');
    $router->put('/petugas/{id}', 'PetugasController@update');
    $router->delete('petugas/{id}', 'PetugasController@destroy');
});

   // User End-Point
    $router->get('/user', 'UserController@index');
    $router->get('/user/{id}', 'UserController@show');
    $router->put('/user/{id}', 'UserController@update');
    $router->delete('/user/{id}', 'UserController@destroy');

    // Keluhan End-Point
    $router->get('/keluhan', 'KeluhanController@index');
    $router->post('/keluhan', 'KeluhanController@store');
    $router->get('/keluhan/{id}', 'KeluhanController@show');
    $router->put('/keluhan/{id}','KeluhanController@update');
    $router->delete('/keluhan/{id}', 'KeluhanController@destroy');
    
    $router->get('/keluhan/image/{id_keluhan}','KeluhanController@image');

    // Saran End-Point
    $router->get('/saran', 'SaranController@index');
    $router->post('/saran', 'SaranController@store');
    $router->get('/saran/{id}', 'SaranController@show');
    $router->delete('/saran/{id}', 'SaranController@destroy');
    $router->put('/saran/{id}', 'SaranController@update');

    // Tanggapan End-Point
    $router->get('/tanggapan', 'TanggapanController@index');
    $router->get('/tanggapan/{id}', 'TanggapanController@show');
    $router->post('/tanggapan', 'TanggapanController@store');
    $router->put('/tanggapan/{id}', 'TanggapanController@update');
    $router->delete('tanggapan/{id}', 'TanggapanController@destroy');



Route::group(['prefix' => 'public'], function () use ($router) {
    // Public Route - Tanggapan
    $router->get('/tanggapan', 'TanggapanController@index');
    $router->get('/tanggapan/{id}', 'TanggapanController@show');

    // Public Route - Saran
    $router->get('/saran', 'SaranController@index');
    $router->get('/saran/{id}', 'SaranController@show');
});