@inject('appLogo', 'App\Http\Controllers\API\SettingController')

<nav-bar :profile="{{ Auth::user() }}"
         applogo={{ $appLogo->getAppLogo() }}></nav-bar>