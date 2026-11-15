<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function testWelcomePageShowsProjectCounts(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('라라벨 Todo 사이트')
            ->assertSee('총 가입자 수');
    }
}
