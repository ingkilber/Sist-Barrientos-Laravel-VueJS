<?php


namespace App\Setup\Manager;


use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManager
{
    public function create(Request $request)
    {
        User::query()->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verified' => 1,
            'is_admin' => 1,
            'user_type' => 'staff',
            'branch_id' =>1,
            'token' => ''
        ]);

        return true;
    }
}