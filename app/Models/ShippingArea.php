<?php



namespace App\Models;

class ShippingArea extends BaseModel
{
    protected $fillable = ['area', 'price'];

    public static function getShippingData($columnName, $columnSortedBy, $limit, $offset)
    {
        $count = ShippingArea::count();
        $data = ShippingArea::select('*')->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

        return ['data' => $data, 'count' => $count];
    }
}
