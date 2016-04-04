<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@index');

Route::get('/home', 'HomeController@index');

Route::auth();

// 사용자가 인증 요청했을 때 깃허브 인증 페이지로 리다이렉트합니다
Route::get('auth/github', 'Auth\AuthController@redirectToGitHub');

//깃허브 인증이 완료되면 깃허브에서 앱 페이지를 호출할 때 실행할 메서드를 등록합니다
Route::get('auth/github/callback', 'Auth\AuthController@handleGitHubCallback');

// 인증을 거쳐야 사용할 수 있는 기능들
Route::group(['middleware' => 'auth'], function()
{
    Route::resource('project', 'ProjectController');
    Route::resource('project.task', 'ProjectTaskController');
    Route::resource('task', 'TaskController', ['only' => [    // 1
        'index', 'show',
    ]]);

    Route::get('/reminder/{userid}/{dueInDays?}', 'ReminderController@sendEmailReminder');
});
