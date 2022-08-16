<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;


class ShortcutKey extends BaseModel
{
    protected $fillable = [
        'user_id', 'defaultShortcuts', 'created_by',
    ];

    public static function getShortcutSettings($userId)
    {
        $checkRecord = ShortcutKey::getOne($userId);
        $shortCutsRecord = [];

        if ($checkRecord == null){

            array_push($shortCutsRecord, [
                'user_id' => $userId,
                'defaultShortcuts' => 'a:7:{s:13:"productSearch";a:3:{s:11:"action_name";s:13:"productSearch";s:12:"shortcut_key";s:6:"ctrl+s";s:6:"status";b:1;}s:8:"holdCard";a:3:{s:11:"action_name";s:8:"holdCard";s:12:"shortcut_key";s:6:"ctrl+h";s:6:"status";b:1;}s:3:"pay";a:3:{s:11:"action_name";s:3:"pay";s:12:"shortcut_key";s:6:"ctrl+p";s:6:"status";b:1;}s:11:"addCustomer";a:3:{s:11:"action_name";s:11:"addCustomer";s:12:"shortcut_key";s:6:"ctrl+a";s:6:"status";b:1;}s:14:"cancelCarditem";a:3:{s:11:"action_name";s:14:"cancelCarditem";s:12:"shortcut_key";s:6:"ctrl+d";s:6:"status";b:1;}s:13:"loadSalesPage";a:3:{s:11:"action_name";s:13:"loadSalesPage";s:12:"shortcut_key";s:6:"ctrl+l";s:6:"status";b:1;}s:12:"donePayment1";a:3:{s:11:"action_name";s:12:"donePayment1";s:12:"shortcut_key";s:6:"ctrl+m";s:6:"status";b:1;}}',
            ]);
            ShortcutKey::insertData($shortCutsRecord);
            $data = ShortcutKey::where('user_id', $userId)->where('customShortcuts', Null)->count();
            if ($data == 1) {
                $data = ShortcutKey::select('defaultShortcuts')->where('user_id', $userId)->get();
                $value = unserialize($data[0]['defaultShortcuts']);
            } else {

                $data = ShortcutKey::select('customShortcuts')->where('user_id', $userId)->get();
                $value = unserialize($data[0]['customShortcuts']);
            }

        }else{
            $data = ShortcutKey::where('user_id', $userId)->where('customShortcuts', Null)->count();
            if ($data == 1) {
                $data = ShortcutKey::select('defaultShortcuts')->where('user_id', $userId)->get();
                $value = unserialize($data[0]['defaultShortcuts']);
            } else {

                $data = ShortcutKey::select('customShortcuts')->where('user_id', $userId)->get();
                $value = unserialize($data[0]['customShortcuts']);
            }
        }

        $shortcutStatus = ShortcutKey::select('shortcutsStatus')->where('user_id', $userId)->get();
        $valueOfStatus = $shortcutStatus[0]['shortcutsStatus'];

        return ['shortcutSettings' => $value, 'shortcutStatus' => $valueOfStatus];

    }

    public static function updateShortcutSettings($userId, $data)
    {
        return ShortcutKey::where('user_id', $userId)->update($data);
    }

}
