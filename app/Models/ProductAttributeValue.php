<?php

namespace App\Models;

class ProductAttributeValue extends BaseModel
{

    protected $fillable = ['product_id', 'attribute_id', 'values'];

    public static function getById($id)
    {
        return ProductAttributeValue::join('product_attributes', 'product_attributes.id', '=', 'product_attribute_values.attribute_id')
            ->select('product_attributes.id', 'product_attribute_values.values')
            ->where('product_attribute_values.product_id', $id)
            ->get();
    }

    public static function attributeValues($productId)
    {
        return ProductAttributeValue::select('attribute_id')->groupBy('attribute_id')->where('product_id', $productId)->get();
    }

    public static function attributeCount($id)
    {
        return ProductAttributeValue::where('attribute_id', $id)->count();
    }
}
