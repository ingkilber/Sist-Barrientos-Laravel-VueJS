<?php

namespace App\Models;

use Auth;

class Setting extends BaseModel
{
    protected $fillable = [
        'setting_name', 'setting_value', 'setting_type', 'user_id', 'created_by',
    ];

    public static function cacheDataSave()
    {
        $allData1 = Cache()->remember('settings', 24 * 60, function () {
            $allData = Setting::all();

            foreach ($allData as $data) {
                if ($data->setting_name == 'offday_setting') {
                    $data->setting_value = explode(',', $data->setting_value);
                }
            }
            return $allData->pluck('setting_value', 'setting_name');
        });
        return $allData1;
    }

    public static function updateSettingData($data)
    {
        foreach ($data as $key => $value) {
            self::updateSetting($key, $value);
        }
    }

    public static function updateSetting($settingName, $settingValue)
    {
        Setting::where('setting_name', $settingName)->update(['setting_value' => $settingValue]);
    }

    public static function getSettingData($settingNameList)
    {
        return Setting::select('setting_name', 'setting_value')->whereIn('setting_name', $settingNameList)->get();
    }

    public static function getSettingValue($name)
    {
        return Setting::select('setting_name', 'setting_value')->where('setting_name', $name)->first();
    }

    public static function inventoryReport()
    {
        return Setting::select('setting_value')->where('setting_name', 're_order_alert')->first()->setting_value;
    }

    public static function currentBranch($id)
    {
        return Setting::select('setting_value')->where('setting_name', 'current_branch')->where('user_id', $id)->first();
    }

    public static function updateCurrentBranch($authID, $branchID)
    {
        Setting::where('setting_name', 'current_branch')->where('user_id', $authID)->update(['setting_value' => $branchID]);
    }

    public static function saveSalesOrReceivingType($type, $orderType)
    {

        $user = Auth::user();
        if ($orderType == 'sales') {
            $settingName =  'sales_type';
        } else {
            $settingName =  'receiving_type';
        }

        $check = Setting::where('setting_name', $settingName)->where('user_id', $user->id)->exists();
        if ($check) {
            Setting::where('setting_name', $settingName)->where('user_id', $user->id)->update(['setting_value' => $type]);
        } else {
            Setting::insert(['setting_name' => $settingName, 'setting_value' => $type, 'user_id' => $user->id]);
        }
    }

    public static function getSaleOrReceivingType($value)
    {
        $user = Auth::user();
        $salesType  = Setting::where('setting_name', $value)->where('user_id', $user->id)->first();
        if (!$salesType) {
            if ($value == 'sales_type') return 'customer';
            else return 'supplier';
        } else {
            return $salesType->setting_value;
        }
    }

    public static function getOneSetting($settingName)
    {
        return Setting::where('setting_name', $settingName)->first();
    }
}
