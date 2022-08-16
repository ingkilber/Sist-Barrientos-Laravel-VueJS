<?php

namespace App\Models;
use DB;

class Customer extends BaseModel
{
    protected $fillable = ['first_name','last_name', 'email', 'company', 'tin_number', 'phone_number', 'address', 'avatar', 'customer_group'];

    public static function getCustomers($searchValue, $typeFilter, $columnName, $columnSortedBy, $limit, $offset, $requestType)
    {

        $query = Customer::query()->leftJoin('customer_groups', 'customers.customer_group', '=', 'customer_groups.id');

        if ($searchValue) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('first_name', 'LIKE', '%' . $searchValue . '%');
                $query->orWhere('last_name', 'LIKE', '%' . $searchValue . '%');
                $query->orWhere('email', 'LIKE', '%' . $searchValue . '%');
            });
        }

        if (!empty($typeFilter)) {
            $query->where('customer_group', $typeFilter);
        }

        $query->select('customers.*', 'customer_groups.title as customer_group_title');
        $count = $query->count();

        if (empty($requestType)) {
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

        } else {
            $data = $query->orderBy($columnName, $columnSortedBy)->get();

        }

        return ['data' => $data, 'count' => $count];
    }

    public static function getCustomerDetails(){
         return Customer::query()->leftJoin('customer_groups', 'customers.customer_group', '=', 'customer_groups.id')
                        ->select(
                            'customers.*',
                            'customer_groups.title as customer_group_title',
                            'customer_groups.discount as customer_group_discount'
                        )
                        ->get();
    }

    public static function customerDetails($id)
    {
        $customerDetails = Customer::where('customers.id', $id)->leftJoin('customer_groups', 'customers.customer_group', '=', 'customer_groups.id')
            ->select('customers.*', 'customer_groups.title as customer_group_title')->first();
        $customerDetails->fullName = $customerDetails->first_name . " " . $customerDetails->last_name;


        return $customerDetails;
    }

    public static function customerData($searchValue)
    {
        $query = Customer::query()->join('customer_groups', 'customers.customer_group', '=', 'customer_groups.id')
            ->select('customers.*','customer_groups.discount as customer_group_discount');

        if ($searchValue) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('first_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchValue . '%');

            });
        }

        return $query->orderBy('id', 'DESC')->get();
    }

    public static function getCustomerGroup($customerGroup)
    {
        $query = CustomerGroup::query()->select('*');

        if ($customerGroup != null) {
            return $query->where('title',$customerGroup)->select('id')->first();
        }
    }

}
