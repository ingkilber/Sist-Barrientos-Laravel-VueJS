<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\PasswordReset;
use App\Models\Setting;
use Illuminate\Http\Request;
use DB, Hash;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Password;
use App\Libraries\Email;
use App\Models\CustomUser;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function verifyUser($token)
    {    $check = User::find($token);

        if (!is_null($check)) {
           $user = User::find($check->id);
            if ($user->verify == 1) {
                return response()->json([
                    'success' => true,
                    'message' => Lang::get('lang.account_already_verified')
                ]);
            }
            $user->update(['verify' => 1]);
           DB::table('users')->where('token', $token)->delete();
            return response()->json([
                'success' => true,
                'message' => Lang::get('lang.successfully_verified')
            ]);
        }
        return response()->json(['success' => false, 'error' => Lang::get('lang.verification_code_invalid')]);
    }

    public function recover(Request $request)
    {
        $this->validate($request, ["email" => "required"]);
        $token = Str::random();
        $email = $request->email;

        $user = CustomUser::getFirst('*', 'email', $email);

        $appName = Setting::getSettingValue('app_name')->setting_value;
        $content = EmailTemplate::getContent();
        $emailSubject = $content->template_subject;

        if ($content->custom_content)
        {
            $text = $content->custom_content;
        }
        else
        {
            $text = $content->default_content;
        }
        $path = \Request::root();
        $link = $path . '/password/reset/' .$token;
        $emailBody = str_replace('{reset_password_link}', $link, str_replace('{app_name}', $appName, $text));

        $emailSend = new Email;

        if(!$user)
        {
            return response()->json(['success' => false, 'error' =>Lang::get('lang.email_not_found')], 500);
        }
        else
        {
            if($emailSend->sendEmail($emailBody, $email, $emailSubject))
            {
               $updateRequest = CustomUser::checkExists('email',$email);
                if($updateRequest)
                {
                    $data = ['email' => $email , 'token' => $token];
                    PasswordReset::insertData($data);
                }
                else
                {
                    PasswordReset::updateValue('email', $email, $token);
                }

                return response()->json([
                    'success' => true, 'data' => ['message' => Lang::get('lang.reset_email_send')]
                ]);

            }else{

                return response()->json(['success' => false, 'error' => Lang::get('lang.email_not_sent')], 500);

            }
        }
    }
}
