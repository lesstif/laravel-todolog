<?php

namespace App\Http\Controllers;

use App\Project;
use App\Task;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class WelcomeController extends Controller
{
    public function __construct()
    {
        //$this->middleware('web');
    }

    public function index()  //2
    {
        $uc = User::count();
        $pc = Project::count();
        $tc = Task::count();

        $total = [ 'user' => $uc,
            'project' => $pc,
            'task' => $tc,
        ];
        return view('welcome')->with('total', $total);
    }

}
