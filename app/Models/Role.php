<?php

namespace App\Models;

class Role extends BaseModel
{
    protected $fillable = ['title', 'permissions', 'created_by'];

    public static function getRole()
    {
        Role::all('id', 'title');
    }

    public static function getRoleList($columnName, $columnSortedBy, $limit, $offset)
    {
        $count = Role::count();
        $data = Role::orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

        return ['data' => $data, 'count' => $count];
    }

}
