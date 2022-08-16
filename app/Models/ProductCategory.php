<?php

namespace App\Models;

class ProductCategory extends BaseModel
{
    protected $table = 'product_categories';

    protected $fillable = ['name', 'created_by'];

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'category_id');
    }

    public static function showCategory()
    {
        return ProductCategory::orderBy('id', 'desc')->get();
    }

    public static function getExistedCategory($category)
    {
        return ProductCategory::select('name', 'id')->where('name', $category)->first();
    }

    public static function insertProductCategory($category, $created_by)
    {
        return ProductCategory::insertGetId(['name' => $category, 'created_by' => $created_by]);
    }

    public static function getProductCategory($column,$request,$limit,$rowOffset,$requestType){

        if(empty($requestType)){
            $data = ProductCategory::orderBy($column, $request)->take($limit)->skip($rowOffset)->get();

        }else{
            $data = ProductCategory::orderBy($column, $request)->get();
        }

        $count = ProductCategory::count();

        return ["data" => $data,'count' => $count];

    }
}
