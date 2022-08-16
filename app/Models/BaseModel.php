<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Hash;

class BaseModel extends Model
{
    public static function index($fields)
    {
        return get_called_class()::select($fields)->get();
    }

    public static function store($data)
    {
        return get_called_class()::create($data);
    }

    public static function getOne($id)
    {
        return get_called_class()::find($id);
    }

    public static function updateData($id, $data)
    {
        return get_called_class()::where('id', $id)->update($data);
    }

    public static function deleteData($id)
    {
        return get_called_class()::where('id', $id)->delete();
    }

    public static function countData()
    {
        return get_called_class()::count();
    }

    public static function allData()
    {
        return get_called_class()::all();
    }

    public static function getInsertedId($data)
    {
        return get_called_class()::insertGetId($data);
    }

    public static function checkExists($column, $id)
    {
        return get_called_class()::where($column, $id)->exists();
    }

    public static function getFirst($fields, $column, $value)
    {
        return get_called_class()::select($fields)->where($column, $value)->first();
    }

    public static function getAll($fields, $column, $value)
    {
        return get_called_class()::select($fields)->where($column, $value)->get();
    }

    public static function deleteRecord($column, $value)
    {
        return get_called_class()::where($column, $value)->delete();
    }

    public static function countRecord($fields, $value)
    {
        return get_called_class()::where($fields, $value)->count();
    }

    public static function updateValue($column, $value, $updateValue)
    {
        return get_called_class()::where($column, $value)->update($updateValue);
    }

    public static function getIdOfExisted($column, $value)
    {
        return get_called_class()::where($column, $value)->first();
    }

    public static function insertData($data)
    {
        return get_called_class()::insert($data);
    }

    public static function getEnrollId($column, $value, $id, $status)
    {
        return get_called_class()::select($column)->where($value, $id)->where('status', $status)->first();
    }

    public static function editData($field, $id, $data)
    {
        return get_called_class()::where($field, $id)->update($data);
    }
    public static function getOrderData($column, $request, $limit, $rowOffset)
    {
        return get_called_class()::orderBy($column, $request)->take($limit)->skip($rowOffset)->get();
    }

    public static function getFirstObject()
    {
        return get_called_class()::select('*')->first();
    }
    public static function getLastObject()
    {
        return get_called_class()::select('*')->orderBy('id', 'desc')->first();
    }
}
