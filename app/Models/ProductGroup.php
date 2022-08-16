<?php

namespace App\Models;

class ProductGroup extends BaseModel
{
    protected $fillable = ['name'];

    public static function getExistedGroup($group)
    {
        return ProductGroup::select('name', 'id')->where('name', $group)->first();
    }

    public static function insertProductGroup($group, $created_by)
    {
        return ProductGroup::insertGetId(['name' => $group, 'created_by' => $created_by]);
    }

    public static function productGroupOrder()
    {
        return ProductGroup::orderBy('id', 'desc')->get();
    }

    public static function getProductGroup($column,$request,$limit,$rowOffset,$requestType){

        if (empty($requestType)){
            $data = ProductGroup::orderBy($column, $request)->take($limit)->skip($rowOffset)->get();

        }else{
            $data = ProductGroup::orderBy($column, $request)->get();
        }
        $count = ProductGroup::count();

        return ["data" => $data,'count' => $count];

    }
}
