<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;

use App\Project;
use App\Task;
use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Requests\StoreTaskRequest;

class ProjectTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($project)
    {
        $proj = Project::findOrFail($project);

        $tasks = $proj->tasks()->get();

        return view('project.task.index')
                ->with('tasks', $tasks)
                ->with('proj', $proj);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($projId)
    {
        $proj = Project::findOrFail($projId);

        return view('project.task.create')
            ->with('proj', $proj);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaskRequest  $request, $projId)
    {
        $task = new Task([    //1
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'priority' => $request->get('priority'),
            'status' => $request->get('status'),
            'due_date' => $request->get('due_date'),
        ]);

        $task->project()->associate($projId);;    //프로젝트 연결

        $task->save();    //DBMS 저장

        if (config('cache.default') === 'redis') {
            Redis::incr('task:count');
        }

        return redirect(route('project.task.index', $task->project->id))
            ->with('message', $task->name . ' 가 생성 되었습니다.');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($projId, $taskId)
    {
        return $this->edit($projId, $taskId);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($projId, $taskId)
    {
        $user = \Auth::user();
        $projects = $user->projects()->get();

        $project = $user->projects()->findOrFail($projId);
        $task = $project->tasks()->findOrFail($taskId);

        return view('project.task.edit')
            ->with('projects', $projects)
            ->with('task', $task);
    }

    /**
     * 태스크 정보 변경
     *
     * @param Request $request
     * @param $projId 프로젝트 id
     * @param $taskId 태스크 id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $projId, $taskId)
    {
        $user = \Auth::user();
        $project = $user->projects()->findOrFail($projId);
        $task = $project->tasks()->findOrFail($taskId);

        $project_id = $request->get('project_id');

        // project id를 변경할 경우
        if ($project_id != $task->project_id) {
            // 변경된 project 검색
            $project = $user->projects()->findOrFail($project_id);
            $task->project()->associate($project);
        }

        $request->validate([
            'name' => 'required|string|max:20',
            'description' => 'nullable|string',
            'priority' => 'required|in:낮음,보통,높음',
            'status' => 'required|in:등록,진행,완료',
            'due_date' => 'nullable|date',
        ]);

        $task->update([        // 2
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'priority' => $request->get('priority'),
            'status' => $request->get('status'),
            'due_date' => $request->get('due_date'),
        ]);

        return redirect(route('project.task.index', $task->project->id))
            ->with('message', $task->name . '가 수정되었습니다.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $projId
     * @param  int  $taskId
     * @return \Illuminate\Http\Response
     */
    public function destroy($projId, $taskId)
    {
        $project = \Auth::user()->projects()->findOrFail($projId);
        $task = $project->tasks()->findOrFail($taskId);

        $task->delete();   // 삭제

        if (config('cache.default') === 'redis') {
            Redis::decr('task:count'); // 갯수 감소
        }

        return redirect(route('project.task.index', $task->project->id))
            ->with('message', $task->name . '이 삭제되었습니다.');
    }
}
