<?php

namespace App\Models;

class Tax extends BaseModel
{
    protected $fillable = ['name', 'percentage', 'is_default'];

    public static function getTotals()
    {
        return Tax::where('is_default', 1)->count();
    }

    public static function getId()
    {
        return Tax::select('id')->where('is_default', 1)->first()->id;
    }

    public static function getTax($columnName, $columnSortedBy, $limit, $offset)
    {
        $count = Tax::count();
        $data = Tax::select('*')->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

        return ['data' => $data, 'count' => $count];
    }

    public static function updateDefault()
    {
        return Tax::where('is_default', 1)->update(['is_default' => 0]);
    }

    public static function getDefaultTaxId()
    {
        return Tax::select('id')->where('is_default', 1)->first()->id;
    }
}
