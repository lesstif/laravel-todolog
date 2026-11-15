<?php

namespace Tests;

use App\Project;
use App\Task;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FormValidationTest extends TestCase
{
    use RefreshDatabase;

    public function testInvalidProjectNamesShowErrorsWithoutChangingTheDatabase(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user, 'user')->create();
        $this->actingAs($user);

        foreach (['', str_repeat('x', 21), ['not a string']] as $name) {
            $this->postJson('/project', ['name' => $name])
                ->assertUnprocessable()->assertJsonValidationErrors('name');
            $this->putJson('/project/'.$project->id, ['name' => $name])
                ->assertUnprocessable()->assertJsonValidationErrors('name');
        }
        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => $project->name]);

        $this->from('/project/create')->post('/project', ['name' => ''])
            ->assertRedirect('/project/create')->assertSessionHasErrors('name');
        $this->withCookie(config('session.cookie'), session()->getId());
        $this->get('/project/create')->assertOk()->assertSee(__('validation.required', ['attribute' => 'name']));
        $this->from('/project/'.$project->id.'/edit')
            ->put('/project/'.$project->id, ['name' => ''])
            ->assertRedirect('/project/'.$project->id.'/edit')->assertSessionHasErrors('name');
        $this->get('/project/'.$project->id.'/edit')->assertOk()
            ->assertSee(__('validation.required', ['attribute' => 'name']));
        $this->put('/project/'.$project->id, ['name' => str_repeat('x', 20)])
            ->assertSessionHasNoErrors()->assertRedirect('/project');
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => str_repeat('x', 20)]);
    }

    public function testInvalidTaskUpdatesShowErrorsAndPreserveTheTask(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user, 'user')->create();
        $task = Task::factory()->for($project)->create();
        $this->actingAs($user);
        $url = '/project/'.$project->id.'/task/'.$task->id;
        $input = [
            'project_id' => $project->id,
            'name' => $task->name,
            'description' => 'Changed description',
            'priority' => '보통',
            'status' => '등록',
            'due_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
        ];
        $before = $task->fresh()->getAttributes();
        foreach ([
            ['name', ''],
            ['name', str_repeat('x', 21)],
            ['name', ['not a string']],
            ['description', ['not a string']],
            ['priority', '즉시'],
            ['status', 'invalid'],
            ['due_date', 'not a date'],
        ] as [$field, $value]) {
            $this->putJson($url, array_merge($input, [$field => $value]))
                ->assertUnprocessable()->assertJsonValidationErrors($field);
            $this->assertSame($before, $task->fresh()->getAttributes());
        }

        // An overdue task must still be editable without changing its deadline.
        $input['name'] = str_repeat('x', 20);
        $input['due_date'] = now()->subDays(2)->format('Y-m-d H:i:s');
        $this->put($url, $input)->assertSessionHasNoErrors()
            ->assertRedirect('/project/'.$project->id.'/task');
        $this->assertSame($input['due_date'], $task->fresh()->due_date->format('Y-m-d H:i:s'));
    }

    public function testInvalidTaskCreationCannotReachDatabaseConstraints(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user, 'user')->create();
        $this->actingAs($user);
        $url = '/project/'.$project->id.'/task';
        $input = [
            'name' => 'Valid task',
            'description' => 'Task description',
            'priority' => '보통',
            'status' => '등록',
            'due_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
        ];

        foreach ([
            ['name', ['not a string']],
            ['description', ['not a string']],
            ['priority', null],
            ['status', 'invalid'],
        ] as [$field, $value]) {
            $this->postJson($url, array_merge($input, [$field => $value]))
                ->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->assertDatabaseCount('tasks', 0);
    }

    public function testInvalidDateFiltersRedirectToTheTaskListWithoutALoop(): void
    {
        $this->actingAs(User::factory()->create());

        foreach ([
            ['start_date', 'invalid'],
            ['end_date', '2026-02-30 12:00:00'],
            ['start_date', ['not a date']],
        ] as [$field, $value]) {
            $query = http_build_query([$field => $value]);
            $this->from('/task?'.$query)->get('/task?'.$query)
                ->assertRedirect('/task')->assertSessionHasErrors($field);
            $this->withCookie(config('session.cookie'), session()->getId());
            $this->get('/task')->assertOk()->assertSee('alert-danger');
            $this->getJson('/task?'.$query)->assertUnprocessable()
                ->assertJsonValidationErrors($field);
        }

        $this->get('/task?'.http_build_query([
            'start_date' => '2026-11-01 00:00:00',
            'end_date' => '2026-11-30 23:59:59',
        ]))->assertOk();
    }
}
