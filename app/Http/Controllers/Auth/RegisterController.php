<?php

namespace App\Http\Controllers\Auth;

use App\Models\Invite;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    protected function register(Request $request)
    {

        $input = $request->all();
        $validator = $this->validator($input);

        if ($validator->passes()) {
            $data = $this->create($input)->toArray();

            $data['token'] = Str::random(25);

            $user = User::find($data['id']);
            $user->token = $data['token'];
            $user->save();

            Mail::send('mails.confirmation', $data, function ($mail) use ($data) {
                $mail->subject(Lang::get('lang.registration_confirmation'));
                $mail->to($data['email']);
            });

            return redirect(route('login'))->with('status', Lang::get('lang.confirmation_email_send'));
        }
        return redirect(route('login'))->with('status', $validator->errors()->toArray());
    }

    public function regForm($token)
    {
        $fields = ['email', 'invited_as'];
        $invitedUser = Invite::getFirst($fields, 'token', $token);

        return view('auth/register',
            [
                'email' => $invitedUser->email,
                'token' => $token
            ]
        );
    }
}
