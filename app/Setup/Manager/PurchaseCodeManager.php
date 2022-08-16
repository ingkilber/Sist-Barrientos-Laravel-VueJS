<?php


namespace App\Setup\Manager;


use Illuminate\Support\Facades\DB;

class PurchaseCodeManager
{
    public function store($code)
    {
        return DB::table('settings')
            ->updateOrInsert([
                'setting_name' => 'purchase_code'
            ],[
                'setting_name' => 'purchase_code',
                'setting_value' => $code
            ]);
    }

    public function getCode()
    {
        return optional(DB::table('settings')
            ->where('setting_name', 'purchase_code')
            ->first())
            ->setting_value;
    }
}
