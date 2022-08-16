<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdjustProductStockType extends BaseModel
{
    protected $fillable=['id', 'title', 'created_by', 'created_at', 'updated_at'];

    public static function getAdjustStockType($columnName, $columnSortedBy, $limit, $offset)
    {
        $count = AdjustProductStockType::count();
        $data =  AdjustProductStockType::orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

        return ['data' => $data, 'count' => $count];
    }

    /*public static function updatePaymentType()
    {
        return PaymentType::where('is_default',1)->update(['is_default' => 0]);
    }*/
}
