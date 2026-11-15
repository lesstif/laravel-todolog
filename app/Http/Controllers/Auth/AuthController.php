<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Redis;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/home');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'mobile_phone', 'min:11', 'max:13'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
        ]);
        if (config('cache.default') === 'redis') {
            Redis::incr('user:count');
        }
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function redirectToGitHub()
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleGitHubCallback(Request $request)
    {
        try {
            $githubUser = Socialite::driver('github')->user();
        } catch (\Laravel\Socialite\Two\InvalidStateException $exception) {
            return redirect('/login')->withErrors(['email' => __('auth.failed')]);
        }

        $user = $this->findOrCreateUser($githubUser);
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('github_id', $user->id);

        return redirect()->intended('/home');
    }

    private function findOrCreateUser($githubUser)
    {
        if ($user = User::where('github_id', $githubUser->getId())->first()) {
            return $user;
        }

        // Do not implicitly link an existing account using an OAuth email.
        if (!$githubUser->getEmail()
            || User::where('email', $githubUser->getEmail())->exists()) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        $user = User::create([
            'name' => $githubUser->getName() ?: $githubUser->getNickname(),
            'email' => $githubUser->getEmail(),
            'github_id' => $githubUser->getId(),
        ]);
        if (config('cache.default') === 'redis') {
            Redis::incr('user:count');
        }

        return $user;
    }
}
