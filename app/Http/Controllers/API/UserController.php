<?php

namespace App\Http\Controllers\API;

use App\Models\OrderItems;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Models\CustomUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Validator, Hash, Mail;
use App\Libraries\searchHelper;

class UserController extends Controller
{
    public $successStatus = 200;

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        $validator = Validator::make($credentials, $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()]);
        }

        $credentials['verified'] = 1;

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $success['token'] = $user->createToken('MyApp')->accessToken;
            return response()->json(['success done' => $success], $this->successStatus);
        } else {
            return response()->json(['error' => Lang::get('lang.inactive_invalid_email')], 401);
        }
    }

    public function logoutApi()
    {
        if (Auth::check()) {
            Auth::user()->authAccessToken()->delete();
            return response()->json(['success' => true, 'message' => Lang::get('lang.logout')]);
        }

        return response()->json(['success' => false, 'error' => Lang::get('lang.logout_failed')], 500);
    }


    public function register(Request $request)
    {
        $this->validate($request, [
            "first_name" => "required",
            "last_name" => "required",
            "email" => "required|unique:users",
            "password" => "required|string|min:6|confirmed",
        ]);

        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $email = $request->email;
        $token = Str::random(25); //Generate verification code
        $password = $request->password;

        CustomUser::store(['first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'token' => $token, 'password' => Hash::make($password)]);

        Mail::send('mails.confirmation', ['first_name' => $first_name, 'token' => $token],
            function ($mail) use ($email, $first_name) {
                $mail->to($email, $first_name);
                $mail->subject('Registration Confirmation');
            });

        return response()->json(['success' => true, 'message' => Lang::get('lang.signup_welcome')]);
    }

    public function confirmation($token)
    {
        $user = CustomUser::userConfirmation($token);

        if (!is_null($user)) {
            $user->verified = 1;
            $user->token = '';
            $user->save();

            return response()->json([
                'success' => true,
                'message' => Lang::get('lang.successfully_verified')
            ]);
        }

        return response()->json(['success' => false, 'error' => Lang::get('lang.verification_code_invalid')]);
    }

    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    public function getUsersList(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        if ($columnName == 'full_name') $columnName = 'first_name';
        else if ($columnName == 'role_title') $columnName = 'title';
        $searchValue = searchHelper::inputSearch($request->searchValue);

        $users = CustomUser::userList($columnName, $request->columnSortedBy, $limit, $request->rowOffset, $searchValue);

        foreach ($users['data'] as $user) {
            $user->full_name = $user->first_name . " " . $user->last_name;
            if ($user->is_admin == 1) $user->role_title = Lang::get('lang.admin');

            if ($user->id == Auth::user()->id) $user->currentuser = true;
        }

        return ['datarows' => $users['data'], 'count' => $users['count']];
    }

    public function views($id)
    {
        $user = CustomUser::getOne($id);
        $response = [
            'message' => 'User information',
            'user' => $user
        ];

        return response()->json($response, 200);
    }

    public function updates(Request $request, $id)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'gender' => 'required',
            'role_id' => 'required'
        ]);

        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $email = $request->input('email');
        $gender = $request->input('gender');
        $role_id = $request->input('role_id');

        $user = CustomUser::with('schedules')->findOrFail($id);

        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->email = $email;
        $user->gender = $gender;
        $user->role_id = $role_id;

        if (!$user->update()) {
            return response()->json([
                'message' => Lang::get('lang.error_update')], 404);
        }

        $response = [
            'message' => Lang::get('lang.update_successful'),
            'user' => $user
        ];

        return response()->json($response, 200);
    }

    public function getRowUser($id)
    {
        if ($rowUser = CustomUser::getFirst(['first_name', 'last_name', 'role_id', 'is_admin', 'branch_id'], 'id', $id)) {
            $branchId = $rowUser->branch_id;
            $branchId = explode(",", $branchId);

            return ['rowUser' => $rowUser,
                'branchId' => $branchId];
        } else {
            $response = [
                'message' => 'This is not a users id',
            ];

            return response()->json($response, 404);
        }
    }


    public function enableUser(Request $request, $id)
    {
        $status = $request->status;
        CustomUser::updateData($id, ['enabled' => $status]);

        if ($status == 0) {
            return response()->json([
                'message' => Lang::get('lang.user_successfully_disabled')], 200);
        }

        if ($status == 1) {
            return response()->json([
                'message' => Lang::get('lang.user_successfully_enabled')], 200);
        }
    }

    public function newAdminUser(Request $request, $id)
    {
        $status = $request->is_admin;

        CustomUser::updateData($id, ['is_admin' => $status]);

        if ($status == 0) {
            return response()->json([
                'message' => Lang::get('lang.user_successfully_removed_from_the_role_as_admin')], 200);
        } else {
            return response()->json([
                'message' => Lang::get('lang.user_successfully_marked_as_admin')], 200);
        }
    }

    public function delete($id)
    {
        $user = CustomUser::getOne($id);
        $schedules = $user->schedules;

        if (!$user->delete()) {
            foreach ($schedules() as $schedule) {
                $user->schedules()->attach($schedule);
            }
            $response = [
                'message' => Lang::get('lang.user_name') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . strtolower(Lang::get('lang.user_name'))
            ];

            return response()->json($response, 200);
        }

        $response = [
            'message' => Lang::get('lang.user_name') . ' ' . Lang::get('lang.successfully_deleted')
        ];

        return response()->json($response, 404);
    }

    public function userDetail($id)
    {
        $tabName = '';
        $routeName= '';
        $perm = new PermissionController;
        $permission = $perm->userDetailsPermission();

        if ($permission) {

            if(isset($_GET['tab_name'])){
                $tabName = $_GET['tab_name'];
            }

            if(isset($_GET['route_name'])){
                $routeName = $_GET['route_name'];
            }

            $userDetails = CustomUser::staffDetails($id);
            $userDetails->fullName = $userDetails->first_name . " " . $userDetails->last_name;

            return view('users/userDetail', [
                'userDetails' => $userDetails,
                'tab_name'=>$tabName,
                'route_name' => $routeName]);

        } else {

            abort(404);
        }

    }

    public function getUser($id)
    {
        $year = date("Y");

        $monthlySale = OrderItems::userSalesRecord($year, $id);

        $monthlyArraySale = $this->manipulateBarChart($monthlySale, 'sales');

        return [ 'sales' => $monthlyArraySale];
    }

    public function manipulateBarChart($chartData, $key)
    {

        $dataArray = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

        foreach ($chartData as $data) {

            $dataArray[$data->month - 1] = $data[$key];
        }

        return $dataArray;
    }

    public function editTax(Request $request, $id)
    {
        if (User::query()->where('id', $id)->update($request->all())) {
            $response = [
                'message' => Lang::get('lang.tax_settings') . ' ' . Lang::get('lang.successfully_updated')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }
}
