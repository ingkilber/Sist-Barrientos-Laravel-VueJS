<?php

namespace App\Http\Controllers\API;

use App\Models\CustomUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;

class RoleAssignController extends Controller
{

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'role_id' => 'required'
        ]);

        $role_id_get = $request->role_id;
        $branchIdGet = $request->branchID;
        $branchIdGet = implode(",", $branchIdGet);
        $data =array();
        $data['role_id'] = $role_id_get;
        $data['branch_id'] = $branchIdGet;

        CustomUser::updateData($id,$data);

        $response = [
            'message' => Lang::get('lang.user_role_successfully_changed'),
        ];

        return response()->json($response, 200);
    }
}
