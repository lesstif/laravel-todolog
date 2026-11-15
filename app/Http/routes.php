<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'WelcomeController@index')->name('welcome');
Route::get('/home', 'HomeController@index')->name('home');

Route::get('/login', 'Auth\AuthController@showLoginForm')->name('login');
Route::post('/login', 'Auth\AuthController@login')->middleware('throttle:6,1');
Route::get('/register', 'Auth\AuthController@showRegistrationForm')->name('register');
Route::post('/register', 'Auth\AuthController@register')->middleware('throttle:6,1');
Route::post('/logout', 'Auth\AuthController@logout')->name('logout');

Route::get('/password/reset', 'Auth\PasswordController@showLinkRequestForm')->name('password.request');
Route::post('/password/email', 'Auth\PasswordController@sendResetLinkEmail')
    ->middleware('throttle:6,1')->name('password.email');
Route::get('/password/reset/{token}', 'Auth\PasswordController@showResetForm')->name('password.reset');
Route::post('/password/reset', 'Auth\PasswordController@reset')
    ->middleware('throttle:6,1')->name('password.update');

Route::get('auth/github', 'Auth\AuthController@redirectToGitHub');
Route::get('auth/github/callback', 'Auth\AuthController@handleGitHubCallback');

Route::group(['middleware' => 'auth'], function () {
    Route::resource('project', 'ProjectController');
    Route::resource('project.task', 'ProjectTaskController');
    Route::resource('task', 'TaskController', ['only' => ['index', 'show']]);

    Route::get('/reminder/{userid}/{dueInDays?}', 'ReminderController@sendEmailReminder');
});
