<?php

namespace Tests;

use App\Project;
use App\Task;
use App\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class ApplicationUpgradeTest extends TestCase
{
    use RefreshDatabase;

    public function testExistingMigrationsAndDemoSeedersWork(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(2, User::count());
        $this->assertSame(8, Project::count());
        $this->assertSame(80, Task::count());
        $this->assertTrue(Task::firstOrFail()->due_date instanceof \Carbon\Carbon);
    }

    public function testProjectAndTaskCrudWork(): void
    {
        config(['cache.default' => 'redis']);
        $user = User::factory()->create();
        $this->actingAs($user);
        Redis::shouldReceive('incr')->once()->with('project:count')->andReturn(1);
        Redis::shouldReceive('incr')->once()->with('task:count')->andReturn(1);
        Redis::shouldReceive('decr')->once()->with('task:count')->andReturn(0);
        Redis::shouldReceive('decr')->once()->with('project:count')->andReturn(0);

        $this->get('/project/create')->assertOk();
        $this->post('/project', ['name' => 'Work', 'description' => 'Work items'])
            ->assertRedirect('/project');
        $project = $user->projects()->firstOrFail();
        foreach (['/project', '/project/'.$project->id, '/project/'.$project->id.'/edit'] as $path) {
            $this->get($path)->assertOk()->assertSee('Work');
        }

        $this->put('/project/'.$project->id, ['name' => 'Updated', 'description' => 'Updated items'])
            ->assertRedirect('/project');
        $taskUrl = '/project/'.$project->id.'/task';
        $this->get($taskUrl.'/create')->assertOk();
        $this->post($taskUrl, [
            'name' => 'New task',
            'description' => 'Task details',
            'priority' => '보통',
            'status' => '등록',
            'due_date' => Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
        ])->assertRedirect($taskUrl);
        $task = $project->tasks()->firstOrFail();
        $this->get($taskUrl)->assertOk()->assertSee('New task');
        $this->get('/task')->assertOk()->assertSee('New task');

        $this->delete($taskUrl.'/'.$task->id)->assertRedirect($taskUrl);
        $this->delete('/project/'.$project->id)->assertRedirect('/project');
        $this->assertDatabaseCount('tasks', 0);
        $this->assertDatabaseCount('projects', 0);
    }

    public function testProjectOwnershipMiddlewareStillProtectsProjects(): void
    {
        $project = Project::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->get('/project/'.$project->id)->assertRedirect('/home');
        $this->get('/project/999')->assertNotFound();
    }

    public function testKoreanValidationMessagesAreLoaded(): void
    {
        $validator = Validator::make([], ['name' => 'required']);

        $this->assertTrue($validator->fails());
        $this->assertNotSame('validation.required', $validator->errors()->first('name'));
        $this->assertStringNotContainsString('field is required', $validator->errors()->first('name'));
    }

    public function testReminderCommandUsesTheConfiguredMailer(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user, 'user')->create();
        Task::factory()->for($project)->create(['due_date' => Carbon::now()->addDays(2)]);

        $this->artisan('reminder:email', ['user' => $user->id, '--due' => '7'])
            ->expectsOutput('1 건의 태스크 알림 메일 전송 완료')
            ->assertExitCode(0);

        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $this->assertSame($user->email, $messages->first()->getOriginalMessage()->getTo()[0]->getAddress());
    }
}
