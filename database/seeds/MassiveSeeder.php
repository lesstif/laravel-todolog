<?php

use Illuminate\Database\Seeder;

/**
 * 테스트를 위한 대량 입력 시더
 *
 * php artisan db:seed --class=MassiveSeeder
 *
 * Class MassiveSeeder
 */
class MassiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		// 수행횟수
		$iteration = 10;

		$userCnt = 5;	// 한 iteration 에서 생성할 사용자 수
		$projectCnt = 5;
		$taskCnt = 10;

		$total = ['user' => 0, 'project' =>0, 'task' => 0];

		for($i = 1; $i < $iteration + 1; $i++) {
			echo "$i 번째 model factory 생성중\n";
			$users = factory(App\User::class, $userCnt)->create();

			$total['user'] += $userCnt;
			foreach($users as $user) {
				foreach(factory(App\Project::class, $projectCnt)->make() as $project) {

					$user->projects()->save($project);

					$total['project'] += 1;

					foreach (factory(App\Task::class, $taskCnt)->make() as $task) {
						$project->tasks()->save($task);

						$total['task'] += 1;
					}
				}
			}
		}

		dump($total);

		//redis에 반영
		if (\Config::get('cache.default') === 'redis') {
			\Redis::set('user:count', App\User::count());
			\Redis::set('project:count', App\Project::count());
			\Redis::set('task:count', App\Task::count());
		}

    }
}
