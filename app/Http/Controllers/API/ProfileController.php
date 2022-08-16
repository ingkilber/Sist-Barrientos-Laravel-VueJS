<?php

namespace App\Http\Controllers\API;

use App\Models\CustomUser;
use App\Models\Setting;
use DateTimeZone;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Libraries\AllSettingFormat;
use App\Libraries\imageHandler;

class ProfileController extends Controller
{
    public function index()
    {
        return response()->json([
            'profile' => Auth::user(),
            'dateformat' => $this->dateFormat(),
        ], 200);
    }

    public function update(Request $request)
    {
        $user_id = Auth::user()->id;

        if ($request->avatar == Auth::user()->avatar) {

            $emailUsed = CustomUser::userEmailExists($user_id, $request->email);
            if ($emailUsed) {
                $userData = ['first_name' => $request->first_name, 'last_name' => $request->last_name,'gender' => $request->gender, 'date_of_birth' => $request->date_of_birth];
                $message=Lang::get('lang.profile').' '.Lang::get('lang.successfully_saved').' '.Lang::get('lang.but_email_already_exists');

            }else{
                $userData = ['first_name' => $request->first_name, 'last_name' => $request->last_name, 'email' => $request->email, 'gender' => $request->gender, 'date_of_birth' => $request->date_of_birth];
                $message=Lang::get('lang.profile').' '.Lang::get('lang.successfully_saved');
            }


            CustomUser::updateData($user_id, $userData);

        } else {
            if ($file = $request->avatar) {
                $imageHandler = new imageHandler;
                $avatar = $imageHandler->imageUpload($request->avatar, 'profile_', 'uploads/profile/');
                $userData['avatar'] = $avatar;

                CustomUser::updateData($user_id, $userData);

                if (Auth::user()->avatar != 'default.jpg') {
                    unlink('uploads/profile/' . Auth::user()->avatar);
                }
            }
            $message=Lang::get('lang.profile').' '.Lang::get('lang.successfully_saved');
        }

        return response()->json(['message' =>$message]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user = Auth::user();
        $password = $request->password;

        CustomUser::updatePassword($user, $password);

        return response()->json(['message' => Lang::get('lang.password_updated')]);
    }


    public function getTimezone()
    {
        return DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    }

    public function dateFormat()
    {
        $allSettingFormat = new AllSettingFormat;

        return $allSettingFormat->getDateFormat();
    }
}