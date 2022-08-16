<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{asset('/js/lang')}}"></script>
    <?php
    if (config('gain.installed') && DB::table('settings')->where('setting_name', '=', 'app_name')->exists()) {
        $app_name = DB::table('settings')->where('setting_name', 'app_name')->select('setting_value')->first()->setting_value;
    } else {
        $app_name = "";
    }

    ?>

    @if(!$app_name)
        <title>@yield('title')</title>
    @else
        <title>@yield('title') - {{$app_name}}</title>
@endif

<!-- Styles -->
<?php
$appDetails = config('gain');
$cssLinks = [
    [
        'comment' => '<!--Import favicon-->',
        'assets' => asset('images/favicon/favicon.png'),
        'rel' => 'shortcut icon',
        'media' => '',
    ],
    [
        'comment' => '<!--Import bootstrap.css-->',
        'assets' => asset('bootstrap/css/bootstrap.css'),
        'rel' => 'stylesheet',
        'media' => "screen,projection"
    ],
    [
        'comment' => '<!--summernote-bs4-->',
        'assets' => asset('summernote-0.8.18/summernote-bs4.css'),
        'rel' => 'stylesheet',
        'media' => '',
    ],
    [
        'comment' => '<!--animate.css-->',
        'assets' => asset('css/animate.css'),
        'rel' => 'stylesheet',
        'media' => '',
    ],
    [
        'comment' => '<!--line-awesome.css-->',
        'assets' => asset('la/css/la.min.css'),
        'rel' => 'stylesheet',
        'media' => '',
    ],
    [
        'comment' => '<!--Import style.css-->',
        'assets' => asset('css/app.css'),
        'rel' => 'stylesheet',
        'media' => 'all',
    ],
];

foreach ($cssLinks as $cssLink) {
    if ($cssLink['comment']) {
        echo $cssLink['comment'] . "\n";
    }

    if ($cssLink['media']) {
        echo "<link href='" . $cssLink['assets'] . '?app_version=' . $appDetails['app_version'] . "' rel='" . $cssLink['rel'] . "' media='" . $cssLink['media'] . "'>\n";
    } else {
        echo "<link href='" . $cssLink['assets'] . '?app_version=' . $appDetails['app_version'] . "' rel='" . $cssLink['rel'] . "'>\n";
    }

}
?>

<!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

</head>
