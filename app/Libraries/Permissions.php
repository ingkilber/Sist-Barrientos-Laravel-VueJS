<?php

namespace App\Libraries;

use App\Models\Role;
use App\User;
use Illuminate\Support\Facades\Auth;

class Permissions
{
    public function isAdmin()
    {
        if (Auth::user()) {
            $user_id = Auth::user()->id;
            $is_admin = User::select('is_admin')->where('id', $user_id)->first();
            if ($is_admin->is_admin == true) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function hasPermission($per)
    {
        $role = Auth::user()->role_id;

        if ($role > 0) {
            $usersPermission = Role::select('permissions')->where('id', $role)->first();
            $usersPermission = $usersPermission->permissions;
            $usersPermission = unserialize($usersPermission);
            if (in_array($per, $usersPermission)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
