<?php

namespace App\Models;

class ProductBrand extends BaseModel
{
    protected $table = 'product_brands';

    protected $fillable = ['name', 'created_by'];

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'brand_id');
    }

    public static function showProductBrand()
    {
        return orderBy('id', 'desc')->get();
    }

    public static function insertProductBrand($brand, $created_by)
    {
        return ProductBrand::insertGetId(['name' => $brand, 'created_by' => $created_by]);
    }

    public static function getProductBrand($column,$request,$limit,$rowOffset,$requestType){

        if (empty($requestType)){
            $data = ProductBrand::orderBy($column, $request)->take($limit)->skip($rowOffset)->get();

        }else{
            $data = ProductBrand::orderBy($column, $request)->get();
        }

        $count = ProductBrand::count();

        return ["data" => $data,'count' => $count];
    }
}
