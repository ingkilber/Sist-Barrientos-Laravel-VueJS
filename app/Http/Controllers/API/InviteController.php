<?php

namespace App\Http\Controllers\API;

use App\Libraries\Email;
use App\Libraries\Permissions;
use App\Models\CustomUser;
use App\Models\EmailTemplate;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Invite;
use App\Models\Role;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Str;
use Validator, Hash;
use Config;

class InviteController extends Controller
{
    public function getRoleId()
    {
        return Role::allData();
    }

    public function permissionCheck()
    {
        return new Permissions;
    }

    public function process(Request $request)
    {
        if ($this->permissionCheck()->isAdmin() || $this->permissionCheck()->hasPermission('can_manage_users')) {
            $user = User::getUser($request->get('email'));
            if (isset($user) && !empty($user)) {
                $response = [
                    'message' => Lang::get('lang.this_email_are_already_exit'),
                ];
                return response()->json($response, 401);
            }

            do {
                $token = Str::random();
            } while (Invite::where('token', $token)->first());

            Invite::store([
                'email' => $request->get('email'),
                'invited_as' => $request->get('invited_as'),
                'invited_branch' => implode(',', $request->get('branchID')),
                'token' => $token
            ]);

            $content = EmailTemplate::getFirst(['template_subject', 'default_content', 'custom_content'], 'template_type', 'user_invitation');
            $subject = $content->template_subject;

            if ($content->custom_content) {
                $text = $content->custom_content;
            } else {
                $text = $content->default_content;
            }

            $path = \Request::root();
            $link = $path . '/accept/' . $token;

            $appName = Setting::getFirst('setting_value', 'setting_name', 'email_from_name')->setting_value;
            $invited_by = Auth::user()->first_name . " " . Auth::user()->last_name;
            $mailText = str_replace('{verification_link}', $link, str_replace('{app_name}', $appName, str_replace('{invited_by}', $invited_by, $text)));
            $email = $request->input('email');

            $emailSend = new Email;
            if (!$emailSend->sendEmail($mailText, $email, $subject)) {
                $response = [
                    'message' => Lang::get('lang.something_went_wrong_can_not_send_the_email'),
                ];
                return response()->json($response, 401);
            } else {
                $response = [
                    'message' => Lang::get('lang.user_invitation_successfully_sent'),
                ];
                return response()->json($response, 200);
            }
        } else {
            $response = [
                'msg' => Lang::get('lang.permission_error'),
                'template' => Lang::get('lang.permission_is_not_available')
            ];
            return response()->json($response, 401);
        }
    }

    public function accept($token)
    {
        $invite = Invite::getFirst('*', 'token', $token);

        if (!is_null($invite)) {
            $invite->is_accepted = 1;
            $invite->save();
            $invite->email;
            $invite->invited_as;

            return redirect('register/' . $token);
        }
    }

    public function invitedRegistration(Request $request, $token)
    {
        $invitedUser = Invite::getFirst(['email', 'invited_as', 'invited_branch'], 'token', $token);
        $email = $invitedUser->email;
        $role_id = $invitedUser->invited_as;
        $branch_id = $invitedUser->invited_branch;

        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'required'
        ]);

        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $password = $request->input('password');

        $inviteReg = CustomUser::store(['first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'token' => $token, 'password' => Hash::make($password), 'role_id' => $role_id, 'user_type' => 'staff', 'branch_id' => $branch_id, 'verified' => 1, 'enabled' => 1]);

        if ($inviteReg->email == $email) {
            Invite::updateValue('token', $token, ['token' => '']);
        }
    }
}
