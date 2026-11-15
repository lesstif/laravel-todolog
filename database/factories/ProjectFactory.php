<?php

namespace Database\Factories;

use App\Project;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->lexify('Project ??????'),
            'description' => fake()->sentence(),
        ];
    }
}
