<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;

use App\Http\Requests;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = \Auth::user();    // 1
        $projects = Project::where('user_id', $user->id)->get();    // 2

        return view('project.index')->with('projects', $projects);    // 3
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('project.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $proj = new Project([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
        ]);

        $user = \Auth::user();

        $proj->user()->associate($user->id);

        $proj->save();

        \Redis::incr('project:count');

        \Log::info('Project 등록 성공',
            ['user-id'=> $user->id, 'project-id'=>$proj->id]
        );

        return redirect('/project')
            ->with('message', $proj->name . ' 이 생성되었습니다.');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $proj = Project::find($id);    //1
        if($proj == null) {
            abort(404, $id . ' 모델을 찾을 수가 없습니다.');    // 2
        }

        return view('project.show')->with('proj', $proj);    //3
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $proj = Project::findOrFail($id);

        return view('project.edit')->with('proj', $proj);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $proj = Project::findOrFail($id);

        $proj->update([    //1
            'name' => $request->get('name'),
            'description' => $request->get('description'),
        ]);

        return redirect('/project')
            ->with('message', $proj->name . '프로젝트가 수정되었습니다.');    //2
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $proj = Project::findOrFail($id);

        foreach($proj->tasks()->get() as $task) {    //자식 레코드 먼저 삭제
            $task->delete();
        }

        $proj->delete();   // 삭제

        \Redis::decr('project:count'); //

        return redirect('/project')
            ->with('message', '프로젝트 ' . $proj->name  . ' 이 삭제되었습니다.');

    }
}
