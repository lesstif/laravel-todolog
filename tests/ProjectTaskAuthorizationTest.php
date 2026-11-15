<?php

namespace Tests;

use App\Project;
use App\Task;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;

class ProjectTaskAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $project;
    protected $otherProject;
    protected $foreignProject;
    protected $task;
    protected $otherTask;
    protected $foreignTask;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'redis']);
        $this->owner = User::create(['name' => 'Owner', 'email' => 'owner@example.test']);
        $otherUser = User::create(['name' => 'Other', 'email' => 'other@example.test']);
        $this->project = $this->createProject($this->owner, 101);
        $this->otherProject = $this->createProject($this->owner, 102);
        $this->foreignProject = $this->createProject($otherUser, 201);
        $this->task = $this->createTask($this->project, 301);
        $this->otherTask = $this->createTask($this->otherProject, 302);
        // This collision catches deletion accidentally using the project ID.
        $this->foreignTask = $this->createTask($this->foreignProject, $this->project->id);

        $this->actingAs($this->owner);
    }

    protected function createProject(User $user, $id)
    {
        $project = new Project(['name' => 'Project '.$id]);
        $project->id = $id;
        $user->projects()->save($project);

        return $project;
    }

    protected function createTask(Project $project, $id)
    {
        $task = new Task([
            'name' => 'Task '.$id,
            'description' => 'Original description',
            'priority' => '보통',
            'status' => '등록',
            'due_date' => '2030-01-01 12:00:00',
        ]);
        $task->id = $id;
        $project->tasks()->save($task);

        return $task;
    }

    protected function taskInput($projectId)
    {
        return [
            'project_id' => $projectId,
            'name' => 'Updated task',
            'description' => 'Updated description',
            'priority' => '높음',
            'status' => '진행',
            'due_date' => '2030-02-01 12:00:00',
        ];
    }

    public static function mismatchedTaskRequests(): array
    {
        $cases = [];
        foreach (['foreignTask', 'otherTask'] as $task) {
            foreach ([
                ['GET', ''],
                ['GET', '/edit'],
                ['PUT', ''],
                ['PATCH', ''],
                ['DELETE', ''],
            ] as $request) {
                $cases[] = [$task, $request[0], $request[1]];
            }
        }

        return $cases;
    }

    #[DataProvider('mismatchedTaskRequests')]
    public function testTaskMustBelongToTheProjectInTheUrl($taskProperty, $method, $suffix)
    {
        $task = $this->$taskProperty;
        $before = Task::findOrFail($task->id)->getAttributes();
        Redis::shouldReceive('decr')->never();

        $response = $this->call($method, '/project/'.$this->project->id.'/task/'.$task->id.$suffix,
            $this->taskInput($this->project->id));

        $response->assertStatus(404);
        $this->assertSame($before, Task::findOrFail($task->id)->getAttributes());
        $this->assertSame(3, Task::count());
    }

    public function testTaskCannotBeMovedToAnotherUsersProject()
    {
        $before = Task::findOrFail($this->task->id)->getAttributes();

        $response = $this->call('PUT', '/project/'.$this->project->id.'/task/'.$this->task->id,
            $this->taskInput($this->foreignProject->id));

        $response->assertStatus(404);
        $this->assertSame($before, Task::findOrFail($this->task->id)->getAttributes());
    }

    public function testTaskCannotBeMovedToAMissingProject()
    {
        $before = Task::findOrFail($this->task->id)->getAttributes();

        $response = $this->call('PUT', '/project/'.$this->project->id.'/task/'.$this->task->id,
            $this->taskInput(999));

        $response->assertStatus(404);
        $this->assertSame($before, Task::findOrFail($this->task->id)->getAttributes());
    }

    public function testOwnerCanViewAndEditTask()
    {
        $url = '/project/'.$this->project->id.'/task/'.$this->task->id;
        foreach ([$url, $url.'/edit'] as $path) {
            $response = $this->call('GET', $path);
            $response->assertOk();
            $this->assertStringContainsString('Task '.$this->task->id, $response->getContent());
        }
    }

    public function testOwnerCanUpdateTaskWithoutMovingIt()
    {
        $response = $this->call('PATCH', '/project/'.$this->project->id.'/task/'.$this->task->id,
            $this->taskInput($this->project->id));

        $response->assertRedirect('/project/'.$this->project->id.'/task');
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'project_id' => $this->project->id,
            'name' => 'Updated task',
        ]);
    }

    public function testOwnerCanMoveTaskButCannotReuseTheOldProjectUrl()
    {
        $oldUrl = '/project/'.$this->project->id.'/task/'.$this->task->id;
        $input = $this->taskInput($this->otherProject->id);
        $response = $this->call('PUT', $oldUrl, $input);

        $response->assertRedirect('/project/'.$this->otherProject->id.'/task');
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'project_id' => $this->otherProject->id,
        ]);
        $before = Task::findOrFail($this->task->id)->getAttributes();

        $response = $this->call('PUT', $oldUrl, $this->taskInput($this->project->id));
        $response->assertStatus(404);
        $this->assertSame($before, Task::findOrFail($this->task->id)->getAttributes());

        $response = $this->call('PUT', '/project/'.$this->otherProject->id.'/task/'.$this->task->id, $input);
        $response->assertStatus(302);
    }

    public function testOwnerCanDeleteOnlyTheTaskNamedInTheUrl()
    {
        Redis::shouldReceive('decr')->once()->with('task:count')->andReturn(2);

        $response = $this->call('DELETE', '/project/'.$this->project->id.'/task/'.$this->task->id);

        $response->assertRedirect('/project/'.$this->project->id.'/task');
        $this->assertDatabaseMissing('tasks', ['id' => $this->task->id]);
        $this->assertDatabaseHas('tasks', [
            'id' => $this->foreignTask->id,
            'project_id' => $this->foreignProject->id,
        ]);
        $this->assertSame(2, Task::count());
    }

    public function testDeletingAMissingTaskDoesNotDeleteTheTaskMatchingTheProjectId()
    {
        Redis::shouldReceive('decr')->never();

        $response = $this->call('DELETE', '/project/'.$this->project->id.'/task/999');

        $response->assertStatus(404);
        $this->assertSame(3, Task::count());
        $this->assertDatabaseHas('tasks', ['id' => $this->foreignTask->id]);
    }
}
