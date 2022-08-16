<?php

namespace App\Models;
use DB;

class Supplier extends BaseModel
{
    protected $fillable = ['last_name','last_name'];

    public static function getSuppliers($column, $request, $limit, $rowOffset, $requestType)
    {
        $query = Supplier::select('id', DB::raw('CONCAT(first_name," ", last_name) AS name'), 'email', 'company', 'phone_number', 'address', 'tin_number');

        $count = $query->count();
        if (empty($requestType)) {
            $data = $query->orderBy($column, $request)->take($limit)->skip($rowOffset)->get();

        } else {
            $data = $query->orderBy($column, $request)->get();
        }

        return ['data' => $data, 'count' => $count];
    }

    public static function supplierData($searchValue)
    {
        $query = Supplier::select('id','first_name','last_name','email','company','phone_number','address');

        if ($searchValue) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('first_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchValue . '%');
            });
        }

        return $query->orderBy('id', 'DESC')->get();
    }

    public static function getAllEmails()
    {
        return Supplier::select('email')->get();
    }
}
