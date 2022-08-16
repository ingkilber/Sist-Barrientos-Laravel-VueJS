<?php

namespace App\Http\Controllers\API;

use App\Models\Role;
use App\Models\CustomUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;

class RoleController extends Controller
{
    public function allData()
    {
        return Role::getRole();
    }

    public function getRolesList(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $offset = $request->rowOffset;

        $roles = Role::getRoleList($columnName, $request->columnSortedBy, $limit, $offset);
        $user = new User();

        foreach ($roles['data'] as $role) {
            if ($user->where('role_id', $role->id)->exists()) {
                $role->used = 1;
            }
        }

        return ['datarows' => $roles['data'], 'count' =>$roles['count']];
    }

    public function getRolePermissions($id)
    {

        $data = Role::getOne($id);
        $permissions = unserialize($data->permissions);
        $data->permissions = $permissions;

        return $data;
    }

    public function getRoleWithout()
    {
        return $roleData = [];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        $title = $request->input('title');
        $permissions = $request->input('permissions');
        $serializePermission = serialize($permissions);
        $created_by = Auth::user()->id;

        if (Role::store([
            'title' => $title,
            'permissions' => $serializePermission,
            'created_by' => $created_by
        ])) {
            $response = [
                'message' => Lang::get('lang.role') . ' ' . Lang::get('lang.successfully_saved')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required'
        ]);

        $title = $request->input('title');
        $permissions = serialize($request->input('permissions'));

        $role = Role::getOne($id);

        if ($role) {
            Role::updateData($id, ['title' => $title, 'permissions' => $permissions]);
            $response = [
                'message' => Lang::get('lang.role') . ' ' . Lang::get('lang.successfully_updated')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    public function delete($id)
    {
        $used = CustomUser::getTotals($id);
        if ($used == 0) {
            Role::deleteData($id);
            $response = [
                'message' => Lang::get('lang.role') . ' ' . Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 200);
        } else {
            $response = [
                'message' => Lang::get('lang.role') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . strtolower(Lang::get('lang.role'))
            ];

            return response()->json($response, 200);
        }
    }
}
