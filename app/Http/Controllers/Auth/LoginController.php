<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Config;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        if (config('gain.installed')) {
            return view('auth.login',
                [
                    'email' => env('IS_DEV') ? 'admin@demo.com' : '',
                    'password' => env('IS_DEV') ? '123456' : '',
                ]);
        } else {
            return redirect('/install');
        }
    }

    protected function validateLogin(Request $request)
    {

        $this->validate($request, [
            $this->username() => [
                'required','string',
                Rule::exists('users')->where(function ($query) {
                    $query->where('verified', true)->where('user_type','staff')->where('enabled',1);
                })
            ],
            'password' => 'required|string',
        ], [
            $this->username() . '.exists' => Lang::get('lang.inactive_invalid_email')
        ]);
    }

    public function login(Request $request)
    {

        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)){
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        throw ValidationException::withMessages([
            $this->username() => [Lang::get('lang.throttle', ['seconds' => $seconds])],
        ])->status(429);
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectPath());
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('lang.email_or_password_is_wrong')],
        ]);
    }

    protected function guard()
    {
        return Auth::guard();
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect('/');
    }

}