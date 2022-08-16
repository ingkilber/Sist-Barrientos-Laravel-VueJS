<?php

namespace App\Models;


class CustomerGroup extends BaseModel
{
    protected $fillable = [
        'title',
        'discount',
        'is_default',
        'created_by'
    ];

    public static function customersGroup()
    {
       return CustomerGroup::select('title as text', 'id as value')->get();
    }

    public static function getCustomerGroup($column,$request,$limit,$rowOffset,$requestType){

        $count = CustomerGroup::count();
        if (empty($requestType)){
            $data =  CustomerGroup::orderBy($column, $request)->take($limit)->skip($rowOffset)->get();

        }else{
            $data =  CustomerGroup::orderBy($column, $request)->get();
        }

        return ['data' => $data, 'count' => $count];
    }

    public static function isCustomerGroupExists($customerGroup)
    {
        return CustomerGroup::where('title',$customerGroup)->count();
    }
}
