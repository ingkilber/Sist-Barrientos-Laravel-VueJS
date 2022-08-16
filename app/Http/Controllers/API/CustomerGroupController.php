<?php

namespace App\Http\Controllers\API;

use App\Libraries\Permissions;
use App\Models\CustomerGroup;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

class CustomerGroupController extends Controller
{
    public function permissionCheck()
    {
        return new Permissions;
    }

    public function index()
    {
        return CustomerGroup::allData();
    }

    public function getGroups(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;
        $requestType = $request->reqType;

        $groups = CustomerGroup::getCustomerGroup($columnName, $request->columnSortedBy,$limit,$request->rowOffset,$requestType);

        foreach ($groups['data'] as $group) {

            if (Customer::checkExists('customer_group', $group->id)) {
                $group->used = 1;
            }

            $group->is_default = $group->is_default == 1 ? 'Yes' : 'No';
        }

        return ['datarows' => $groups['data'], 'count' => $groups['count']];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            "title" => "required",
        ]);

        if($request->discount == null) {
            $request->discount = 0;
        }

        $customerGroup = new CustomerGroup();

        if ($is_default = $request->input('is_default') == 1) {
            CustomerGroup::updateValue('is_default', 1, ['is_default' => 0]);
        }

        $created_by = Auth::user()->id;

        if ($group = $customerGroup->store([
            "title" => $request->input('title'),
            "discount" => $request->discount,
            "is_default" => $is_default,
            "created_by" => $created_by,
        ])) {
            $response = [
                'message' => Lang::get('lang.customer_group') . ' ' . Lang::get('lang.successfully_saved')
            ];

            return response()->json($response, 201);
        }

        $response = [
            'message' => Lang::get('lang.getting_problems')
        ];

        return response()->json($response, 404);
    }

    public function show($id)
    {
        return CustomerGroup::getOne($id);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            "title" => "required",
        ]);

        if($request->discount == null) {
            $request->discount = 0;
        }

        if ($request->input('is_default') == 1) {
            CustomerGroup::query()->where('is_default', 1)->update(['is_default' => 0]);
        }

        if ($group = CustomerGroup::getOne($id)) {
            $group->title = $request->input('title');
            $group->discount = $request->discount;
            $group->is_default = $request->input('is_default');
            $group->save();

            $response = [
                'message' => Lang::get('lang.customer_group') . ' ' . Lang::get('lang.successfully_updated')
            ];

            return response()->json($response, 201);
        }
        $response = [
            'message' => Lang::get('lang.getting_problems')
        ];

        return response()->json($response, 404);
    }

    public function destroy($id)
    {
        $group = CustomerGroup::getOne($id);
        $user = Customer::query()->where('customer_group', $group->id)->count();

        if ($user == 0) {
            CustomerGroup::deleteData($id);
            $response = [
                'message' => Lang::get('lang.customer_group') . ' ' . Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 200);
        } else {
            $response = [
                'message' => Lang::get('lang.customer_group') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . strtolower(Lang::get('lang.customer_group'))
            ];

            return response()->json($response, 200);
        }
    }

    public function getCustomerGroups()
    {
        $customerGroups = CustomerGroup::customersGroup();
        return ['customerGroups' => $customerGroups];
    }
}