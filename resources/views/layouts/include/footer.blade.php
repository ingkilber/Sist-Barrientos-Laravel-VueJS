@inject('app', 'App\Http\Controllers\API\SettingController')
<!-- Scripts -->
<?php
$publicPath = $app->getAppPublicPath();
$appDetails = config('gain');
$scriptSources=[
    [
        'comment' =>'<!--Import app.js-->',
        'src' => asset($publicPath.'/js/app.js'),
    ],
    [
        'comment' =>'<!--Import ActionButton.js-->',
        'src' => asset($publicPath.'/js/ActionButton.js'),
    ],
    [
        'comment' =>'<!--Import accounting.js-->',
        'src' => asset($publicPath.'/js/accounting.js'),
    ],
    [
        'comment' =>'<!--Import summernote-lite.js-->',
        'src' => asset($publicPath.'/summernote-0.8.18/summernote-bs4.js'),
    ],


    [
        'comment' =>'<!-- Import Excel js too import excel file -->',
        'src' => asset($publicPath.'/js/xlsx.js'),
    ],
    [
        'comment' =>'<!-- Import Excel js to import excel file -->',
        'src' => asset($publicPath.'/js/jszip.js'),
    ],
    [
        'comment' =>'<!-- Import Excel js too import excel file -->',
        'src' => asset($publicPath.'/js/xlsx.full.min.js'),
    ],
    [
        'comment' =>'<!-- Import print functionality -->',
        'src' => asset($publicPath.'/js/print.min.js'),
    ],

];
foreach ($scriptSources as $scriptSource)
{
    if ($scriptSource['comment'])
    {
        echo $scriptSource['comment']."\n";
    }

    echo "<script src='" .$scriptSource['src']. "?app_version=".$appDetails['app_version']. "'></script> \n";

}
?>
<script src="https://checkout.stripe.com/checkout.js"></script>

</body>
</html>