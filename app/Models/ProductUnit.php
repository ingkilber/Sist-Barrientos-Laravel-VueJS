<?php

namespace App\Models;

class ProductUnit extends BaseModel
{

    protected $fillable = ['name', 'short_name'];

    public function product()
    {
        return $this->belongsTo('App\Models\ProductUnit', 'unit_id');
    }

    public static function getUnitData($request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $rowLimit = $request->rowLimit;
        $offset = $request->rowOffset;
        $requestType = $request->reqType;

        if (empty($requestType)) {
            $data = ProductUnit::orderBy($columnName, $request->columnSortedBy)->take($rowLimit)->skip($offset)->get();

        }else{
            $data = ProductUnit::orderBy($columnName, $request->columnSortedBy)->get();
        }
        $count = ProductUnit::count();

        return ['data' => $data, 'count' => $count];
    }

    public static function idOfExisted($fields, $column, $value)
    {
        return ProductUnit::select($fields)->where($column[0], $value[0])->orWhere($column[1], $value[1])->first();
    }

}
