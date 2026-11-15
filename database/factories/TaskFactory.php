<?php

namespace Database\Factories;

use App\Project;
use App\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->lexify('Task ??????'),
            'description' => fake()->text(),
            'priority' => '보통',
            'status' => '등록',
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
