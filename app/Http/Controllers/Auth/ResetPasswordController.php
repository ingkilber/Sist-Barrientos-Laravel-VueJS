<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CustomUser;
use App\Models\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Validator, Hash;
use Illuminate\Support\Facades\Lang;

class ResetPasswordController extends Controller
{


    use ResetsPasswords;

    protected $redirectTo = '/dashboard';


    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showResetForm(Request $request, $token = null)
    {
        $data = PasswordReset::getFirst('email','token', $token);

        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $data->email]
        );
    }

    public function reset(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
            'password_confirmation' => 'required',
        ]);
        
        $email = $request->email;
        $password =['password' => Hash::make($request->password)];
        $token = $request->token;
        $emailForPass = PasswordReset::getFirst('email','token',$token);

        if($emailForPass->email == $email)
        {
            CustomUser::updateValue('email',$email,$password);
            PasswordReset::deleteRecord('email',$email);

            $response = [
                'message' => Lang::get('lang.password_successfully_changed'),
            ];

            return response()->json($response, 200);
        }

        $response = [
            'message' => Lang::get('lang.something_wrong'),
        ];
        return response()->json($response, 404);
    }
}
