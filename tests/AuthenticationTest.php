<?php

namespace Tests;

use App\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GithubUser;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthenticationPagesRender(): void
    {
        foreach (['/login', '/register', '/password/reset'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function testGuestsCannotAccessProjects(): void
    {
        $this->get('/project')->assertRedirect('/login');
        $this->getJson('/project')->assertUnauthorized();
    }

    public function testUserCanRegisterLoginAndLogout(): void
    {
        config(['cache.default' => 'redis', 'cache.limiter' => 'array']);
        Redis::shouldReceive('incr')->once()->with('user:count')->andReturn(1);

        $this->post('/register', [
            'name' => 'New user',
            'email' => 'new@example.test',
            'phone' => '01012345678',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect('/home');

        $user = User::where('email', 'new@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('01012345678', $user->phone);
        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->get('/home')->assertOk()->assertSee('You are logged in!');

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'new-password',
        ])->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
        $this->get('/logout')->assertStatus(405);
    }

    public function testInvalidPasswordDoesNotLogIn(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function testSeventhLoginAttemptIsRateLimited(): void
    {
        for ($attempt = 0; $attempt < 6; ++$attempt) {
            $this->post('/login', [
                'email' => 'missing@example.test',
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $this->post('/login', [
            'email' => 'missing@example.test',
            'password' => 'wrong-password',
        ])->assertStatus(429)->assertHeader('Retry-After');
    }

    public function testPasswordResetNotificationAndTokenWork(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/password/email', ['email' => $user->email])
            ->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->get('/password/reset/'.$token.'?email='.urlencode($user->email))
            ->assertOk()
            ->assertSee($user->email)
            ->assertSee($token);

        $input = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'reset-password',
            'password_confirmation' => 'reset-password',
        ];
        $this->post('/password/reset', $input)->assertRedirect('/home');
        $this->assertTrue(Hash::check('reset-password', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);

        $this->post('/logout');
        $this->post('/password/reset', $input)->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function testGithubCallbackLogsInAnExistingUser(): void
    {
        $user = User::factory()->create(['github_id' => 12345]);
        $this->mockGithubUser([
            'id' => '12345',
            'name' => 'Github user',
            'nickname' => 'github-user',
            'email' => $user->email,
        ]);

        $this->get('/auth/github/callback')->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());
    }

    public static function counterCacheStores(): array
    {
        return [['array'], ['file'], ['redis']];
    }

    #[DataProvider('counterCacheStores')]
    public function testGithubCallbackCanCreateAnAccount(string $cacheStore): void
    {
        config(['cache.default' => $cacheStore]);
        if ($cacheStore === 'redis') {
            Redis::shouldReceive('incr')->once()->with('user:count')->andReturn(1);
        } else {
            Redis::shouldReceive('incr')->never();
        }
        $this->mockGithubUser([
            'id' => '12345',
            'name' => null,
            'nickname' => 'github-user',
            'email' => 'github@example.test',
        ]);

        $this->get('/auth/github/callback')->assertRedirect('/home');

        $user = User::where('github_id', 12345)->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('github-user', $user->name);
    }

    public function testGithubEmailCannotImplicitlyLinkAnExistingAccount(): void
    {
        $user = User::factory()->create();
        $this->mockGithubUser([
            'id' => '12345',
            'name' => 'Github user',
            'nickname' => 'github-user',
            'email' => $user->email,
        ]);

        $this->get('/auth/github/callback')->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertNull($user->fresh()->github_id);
        $this->assertSame(1, User::count());
    }

    public function testGithubCallbackRejectsAnInvalidState(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andThrow(new InvalidStateException());
        Socialite::shouldReceive('driver')->with('github')->once()->andReturn($provider);

        $this->get('/auth/github/callback')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function testProductionLogoutRequiresACsrfToken(): void
    {
        $this->actingAs(User::factory()->create());
        $this->app->detectEnvironment(fn () => 'production');

        $this->post('/logout')->assertStatus(419);
        $this->assertAuthenticated();

        $this->withSession(['_token' => 'csrf-test-token'])
            ->post('/logout', ['_token' => 'csrf-test-token'])
            ->assertRedirect('/');
        $this->assertGuest();
    }

    protected function mockGithubUser(array $attributes): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn((new GithubUser())->map($attributes));
        Socialite::shouldReceive('driver')->with('github')->once()->andReturn($provider);
    }
}
