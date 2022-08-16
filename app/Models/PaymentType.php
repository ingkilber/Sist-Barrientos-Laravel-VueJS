<?php

namespace App\Models;

class PaymentType extends BaseModel
{

    protected $fillable=['name','type','status','is_default','created_by'];

    public static function getPaymentType($columnName, $columnSortedBy, $limit, $offset)
    {
        $count = PaymentType::count();
        $data =  PaymentType::orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

        return ['data' => $data, 'count' => $count];
    }

    public static function updatePaymentType()
    {
        return PaymentType::where('is_default',1)->update(['is_default' => 0]);
    }
}
