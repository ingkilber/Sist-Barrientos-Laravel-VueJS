<?php
namespace App\Http\Controllers;
use App\Models\Setting;
use App\Models\ShortcutKey;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Schema;
use App\Libraries\AllSettingFormat;
use Config;
use Illuminate\Support\Facades\Auth;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $userss;
    public $settingConfig;
    public $appUrl;
    public $publicPath;
    public $app_name;
    public $sender_mail;
    public function __construct()
    {
        $installCheck = config('gain.installed');
        $this->appUrl = \Request::root();
        $this->publicPath = $this->appUrl;
        $this->userss = Auth::user();

        if ($installCheck == true) {
            $allSettingFormat = new AllSettingFormat;
            if (Schema::hasTable('settings')) {
                $settings = Setting::all();
                foreach ($settings as $setting) {
                    Config::set($setting->setting_name, $setting->setting_value);
                }
            }

            $this->app_name = Config::get('app_name');
            $this->sender_mail = Config::get('email_from_address');
            $this->userss = Auth::user();
            $userId = 0;
            if ($this->userss){
                $userId = $this->userss['id'];
            }
            $data = ShortcutKey::where('user_id', $userId)->where('customShortcuts', Null)->count();
            if ($data == 1) {
                $data = ShortcutKey::select('defaultShortcuts', 'shortcutsStatus')->where('user_id', $userId)->first();
            } else {
                $data = ShortcutKey::select('customShortcuts', 'shortcutsStatus')->where('user_id', $userId)->first();
            }
            $shortCuts = false;
            if ($data){
                $shortCuts = $data['customShortcuts'] ? unserialize($data['customShortcuts']) : unserialize($data['defaultShortcuts']);
                Config::set('overAllShortcutStatus', $data['shortcutsStatus']);
            }
            if ($shortCuts != false) {
                foreach ($shortCuts as $shortCut) {
                    Config::set($shortCut['action_name'], $shortCut['shortcut_key']);
                    Config::set($shortCut['action_name'] . '_status', $shortCut['status']);
                }
            }
            Config::set('dateFormat', $allSettingFormat->getDateRangeFormat());
        }
    }


}