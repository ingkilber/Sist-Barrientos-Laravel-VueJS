<?php

namespace App\Models;

class ProductAttribute extends BaseModel
{
    protected $fillable = ['name', 'created_by'];
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
    public static function showAttribute()
    {
        return ProductAttribute::orderBy('id', 'desc')->get();
    }
    public static function getProductAttribute($column, $request, $limit, $rowOffset, $requestType)
    {
        if (empty($requestType)) {
            $data = ProductAttribute::orderBy($column, $request)->take($limit)->skip($rowOffset)->get();

        } else {
            $data = ProductAttribute::orderBy($column, $request)->get();
        }
        $count = ProductAttribute::count();
        return ["data" => $data, 'count' => $count];
    }
}
