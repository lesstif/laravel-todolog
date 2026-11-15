<?php

namespace Tests;

use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\DomCrawler\Crawler;

class RuntimeSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function testEveryOfferedTaskPriorityCanBeSaved(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user, 'user')->create();
        $this->actingAs($user);
        $taskUrl = '/project/'.$project->id.'/task';
        $response = $this->get($taskUrl.'/create')->assertOk();
        $response->assertSee('bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css');
        $priorities = (new Crawler($response->getContent()))
            ->filter('select[name="priority"] option')->extract(['value']);

        $this->assertSame(['낮음', '보통', '높음'], $priorities);
        foreach ($priorities as $index => $priority) {
            $this->post($taskUrl, [
                'name' => 'Priority '.$index,
                'description' => 'Priority test',
                'priority' => $priority,
                'status' => '등록',
                'due_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            ])->assertSessionHasNoErrors()->assertRedirect($taskUrl);
            $this->assertDatabaseHas('tasks', [
                'project_id' => $project->id,
                'name' => 'Priority '.$index,
                'priority' => $priority,
            ]);
        }
    }

    public function testFileCacheSupportsRegistrationAndProjectTaskCrudWithoutRedis(): void
    {
        config(['cache.default' => 'file', 'cache.limiter' => 'array']);
        Redis::shouldReceive('connection')->never();
        Redis::shouldReceive('incr')->never();
        Redis::shouldReceive('decr')->never();
        Redis::shouldReceive('get')->never();

        $this->get('/')->assertOk()->assertViewHas('total', [
            'user' => 0, 'project' => 0, 'task' => 0,
        ]);
        $this->post('/register', [
            'name' => 'Local user',
            'email' => 'local@example.test',
            'phone' => '01000000000',
            'password' => 'local-test-password',
            'password_confirmation' => 'local-test-password',
        ])->assertRedirect('/home');
        $this->assertAuthenticatedAs(User::firstOrFail());

        $this->post('/project', ['name' => 'Local project'])->assertRedirect('/project');
        $project = Project::firstOrFail();
        $this->put('/project/'.$project->id, ['name' => 'Updated project'])
            ->assertRedirect('/project');

        $taskUrl = '/project/'.$project->id.'/task';
        $input = [
            'project_id' => $project->id,
            'name' => 'Local task',
            'description' => 'Local task details',
            'priority' => '보통',
            'status' => '등록',
            'due_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
        ];
        $this->post($taskUrl, $input)->assertRedirect($taskUrl);
        $task = $project->tasks()->firstOrFail();
        $this->put($taskUrl.'/'.$task->id, array_merge($input, ['name' => 'Updated task']))
            ->assertRedirect($taskUrl);
        $this->get('/task')->assertOk()->assertSee('Updated task');
        $this->get('/')->assertOk()->assertViewHas('total', [
            'user' => 1, 'project' => 1, 'task' => 1,
        ]);

        $this->delete($taskUrl.'/'.$task->id)->assertRedirect($taskUrl);
        $this->delete('/project/'.$project->id)->assertRedirect('/project');
        $this->get('/')->assertOk()->assertViewHas('total', [
            'user' => 1, 'project' => 0, 'task' => 0,
        ]);
        $this->post('/logout')->assertRedirect('/');
        $this->post('/login', [
            'email' => 'local@example.test',
            'password' => 'local-test-password',
        ])->assertRedirect('/home');
        $this->assertAuthenticated();
    }
}
