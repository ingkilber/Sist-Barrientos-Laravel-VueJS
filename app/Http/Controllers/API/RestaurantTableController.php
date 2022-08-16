<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;

class RestaurantTableController extends Controller
{
    public function index()
    {
        $authUser = Auth::user();

        if ($authUser->is_admin == 1) {
            $data = RestaurantTable::index('*');
        } else {
            $tableId = $authUser->table_id;
            $data = RestaurantTable::getRestaurantTable($tableId);
        }

        return $data;
    }

    public function getRowTable($id)
    {
        return RestaurantTable::getOne($id);
    }

    public function getTableList(Request $request)
    {
        $table = RestaurantTable::getTableList($request);
        foreach ($table['data'] as $rowTable) {
            if (Order::checkExists('table_id', $rowTable->id)) {
                $rowTable->used = 1;
            }
        }
        return ['datarows' => $table['data'], 'count' => $table['count']];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'branch_id' => 'required',
        ]);

        RestaurantTable::store([
            'name' => $request->name,
            'branch_id' => $request->branch_id,
            'created_by' => Auth::user()->id
        ]);

        $response = [
            'message' => Lang::get('lang.table') . ' ' . Lang::get('lang.successfully_added')
        ];

        return response()->json($response, 200);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'branch_id' => 'required',
        ]);

        $data = array();
        $data['name'] = $request->name;
        $data['branch_id'] = $request->branch_id;
        $data['created_by'] = Auth::user()->id;

        if (RestaurantTable::updateData($id, $data)) {
            $response = [

                'message' => ucfirst(strtolower(Lang::get('lang.table_details') . ' ' . Lang::get('lang.successfully_updated')))
            ];

            return response()->json($response, 200);
        }
    }

    public function deleteTable($id)
    {
        RestaurantTable::deleteData($id);
        $response = [
            'message' => Lang::get('lang.table') . ' ' . Lang::get('lang.successfully_deleted')
        ];

        return response()->json($response, 200);
    }
}
