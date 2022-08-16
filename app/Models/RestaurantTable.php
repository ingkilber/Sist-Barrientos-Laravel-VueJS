<?php

namespace App\Models;

use Auth;
use App\Libraries\searchHelper;
class RestaurantTable extends BaseModel
{
    protected $fillable = ['name', 'branch_id'];

    public static function getRestaurantTable($tableId)
    {
        return RestaurantTable::select('*')->whereIn('id', explode(',', $tableId))->get();
    }

    public static function getTableList($request)
    {

        $searchValue = searchHelper::inputSearch($request->searchValue);


        $query = RestaurantTable::join('branches', 'branches.id', 'restaurant_tables.branch_id')
            ->select('restaurant_tables.id', 'restaurant_tables.name as name', 'branches.name as branchName', 'branches.id as branchId');

        if (searchHelper::inputSearch($request->searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('restaurant_tables.name', 'LIKE', '%' . $searchValue . '%');
            });
        }

        if (!empty($request->filtersData)) {
            $query->where('restaurant_tables.branch_id', $request->filtersData[0]['value']);
        }

        $count = $query->count();

        if (empty($requestType)) {
            $data = $query->orderBy($request->columnKey, $request->columnSortedBy)->take($request->rowLimit)->skip($request->rowOffset)->get();

        } else {
            $data = $query->orderBy($request->columnKey, $request->columnSortedBy)->get();

        }
        return ['data' => $data, 'count' => $count];
    }

    public static function tableTypeFilter($columnName, $limit, $offset, $columnSortedBy, $branchId)
    {
        return RestaurantTable::leftJoin('branches', 'branches.id', 'restaurant_tables.branch_id')
            ->select('restaurant_tables.id as table_id', 'restaurant_tables.name as name', 'branches.name as branchName')
            ->where('restaurant_tables.branch_id', $branchId)
            ->orderBy($columnName, $columnSortedBy)
            ->take($limit)
            ->skip($offset)
            ->get();
    }

    public static function updateTableStatus($tableID, $status)
    {
        RestaurantTable::where('id', $tableID)->update(['status' => $status]);
    }
}
