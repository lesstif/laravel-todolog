<?php

namespace App\Http\Controllers;

use App\Project;
use App\Task;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;

use Redis;

class WelcomeController extends Controller
{
    public function __construct()
    {
        //$this->middleware('web');
    }

    public function index()
    {
        // 현재 캐시 드라이버 설정 확인
        $drv = \Config::get('cache.default');

        if ($drv === 'redis') { // redis 일 경우
            $userCount = Redis::get('user:count');
            $projectCount= Redis::get('project:count');
            $taskCount= Redis::get('task:count');
        } else {        // 아닐 경우 DB 에서 읽어 옴.
            $userCount = User::count();
            $projectCount = Project::count();
            $taskCount = Task::count();
        }

        $total = [ 'user' => $userCount,
            'project' => $projectCount,
            'task' => $taskCount,
        ];
        return view('welcome')->with('total', $total);

    }

}
