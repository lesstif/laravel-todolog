<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Project::class, function (Faker\Generator $faker) {
    // 집계 함수를 이용해 id의 최솟값과 최댓값을 가져옴
    $min = App\User::min('id');  // 1
    $max = App\User::max('id');
    return [
        'user_id' => $faker->numberBetween($min, $max),    // 2
        'name' => substr($faker->word, 0, 20),            // 3
        'description' => $faker->sentence,                // 4
        'created_at' => $faker->dateTimeBetween($startDate = '-2 years', $endDate = '-1 years'),    //5
        'updated_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now'),            //6
    ];
});

$factory->define(App\Task::class, function (Faker\Generator $faker) {
    // 집계 함수를 이용해 id의 최솟값과 최댓값을 가져옴
    $min = App\Project::min('id');
    $max = App\Project::max('id');

    $dt = $faker->dateTimeBetween($startDate = '-1 months', $endDate = 'now');    // 7

    return [
        'project_id' => $faker->numberBetween($min, $max),
        'name' => substr($faker->sentence, 0, 20),
        'description' => $faker->text,
        'due_date' => $faker->dateTimeBetween($startDate = '-1 months', $endDate = '+1 months'),    // 8
        'created_at' => $dt,
        'updated_at' => $dt,
    ];
});
