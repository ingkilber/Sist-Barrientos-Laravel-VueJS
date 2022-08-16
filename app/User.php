<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as FacadesDB;
//use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

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
        'customer_code',
        'phone_number',
        'address',
        'user_type',
        'enabled',
        'created_by',
        'customer_group'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function authAccessToken()
    {
        return $this->hasMany('\App\OauthAccessToken');
    }

    public function getFileAttribute($avatar)
    {
        return $this->uploads . $avatar;
    }

    public static function getTotals($id)
    {
        User::where('role_id', $id)->count();
    }

    public static function getUsers($searchValue)
    {
        return User::join('customer_groups', 'users.customer_group', '=', 'customer_groups.id')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email',  'customer_groups.discount as customer_group_discount')
            ->where('users.first_name', 'LIKE', '%' . $searchValue . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $searchValue . '%')
            ->orWhere('users.email', 'LIKE', '%' . $searchValue . '%')
            ->orderBy('id', 'DESC')->get();
    }

    public static function updatePassword($user, $password)
    {
        $user->password = Hash::make($password);
        $user->save();
    }

    public static function checkUserToken($token)
    {
        return User::where('token', $token)->first();
    }

    public static function getUser($email)
    {
        return User::where('email', $email)->first();
    }

    public static function getUserList()
    {
        return User::select('id', FacadesDB::raw("CONCAT(users.first_name,' ',users.last_name)  as branch_manager"))->get();
    }
    public static function getUserById($userId)
    {
        return User::select('id', FacadesDB::raw("CONCAT(users.first_name,' ',users.last_name)  as branch_manager"))->where('id', $userId)->first();
    }
}
