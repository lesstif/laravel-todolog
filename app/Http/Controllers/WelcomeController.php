<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class WelcomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('web');
    }

    public function index()  //2
    {
        // 3 사용자, 프로젝트, 태스크 수 가져오기. 아직 모델을 생성하지 않았으므로 0으로 설정
        $uc = 0; //User::count();
        $pc = 0; //Project::count();
        $tc = 0; //Task::count();

        $total = [ 'user' => $uc,
            'project' => $pc,
            'task' => $tc,
        ];
        return view('welcome')->with('total', $total);
    }

}
