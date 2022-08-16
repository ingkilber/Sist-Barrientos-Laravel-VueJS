<?php

namespace App\Models;

use App\User;
use Illuminate\Http\Request;
use DB;
use Hash;


class CustomUser extends BaseModel
{
    protected $table = 'users';
    protected $uploads = '/uploads/profiles';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'gender',
        'avatar',
        'date_of_birth',
        'verified',
        'role_id',
        'branch_id',
        'token',
        'is_admin',
        'notification_check',
        'company',
        'phone_number',
        'address',
        'user_type',
        'enabled',
        'created_by',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function userInfo($id)
    {
        return CustomUser::select(DB::raw('concat(first_name," ",last_name) as full_name'))->where('id', $id)->first()->full_name;
    }

    public static function supplierData()
    {
        return CustomUser::where('user_type', 'supplier')
            ->select('id', DB::raw('CONCAT(first_name," ", last_name) AS name'), 'email', 'company', 'phone_number', 'address')
            ->get();
    }

    public static function supplier()
    {
        return CustomUser:: where('user_type', 'supplier')->count();
    }

    public static function getTotals($id)
    {
        CustomUser::where('role_id', $id)->count();
    }

    public static function updatePassword($user, $password)
    {
        $user->password = Hash::make($password);
        $user->save();
    }

    public static function getUser($email)
    {
        return $user = CustomUser::getFirst('*', 'email', $email);
    }

    public static function userConfirmation($token)
    {
        CustomUser::getFirst('*', 'token', $token);
    }

    public static function userList($columnName, $columnSortedBy, $limit, $offset, $searchValue)
    {
        if ($searchValue) {
            $query = CustomUser::select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.enabled', 'users.is_admin', 'roles.title as role_title')
                ->where('roles.title', 'LIKE', '%' . $searchValue . '%')
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->where('user_type', 'staff')
                ->where('title', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('first_name', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('email', 'LIKE', '%' . $searchValue . '%');
        } else {
            $query = CustomUser::select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.enabled', 'users.is_admin', 'roles.title')
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->where('user_type', 'staff');

        }

        $count = $query->count();
        $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

        return ['data' => $data, 'count' => $count];
    }

    public static function staffDetails($id)
    {
        return CustomUser::leftJoin('roles', 'users.role_id', '=', 'roles.id')->where('user_type', '=', 'staff')->where('users.id', $id)->select('users.*','roles.title')->first();
    }

    public static function userEmailExists($userId, $email)
    {
        return CustomUser::where('email', $email)->where('id', '!=', $userId)->exists();
    }
}
