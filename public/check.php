<?php

$requirements = require_once "requirements.php";
$has_error = false;
foreach ($requirements as $key => $requirement) {
    if (!$requirements[$key]) $has_error = true;
}

if (!$has_error){
    header('Location: //'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']));
    exit();
}

$strOk = '<i class="fa fa-fw fa-check-circle-o row-icon" aria-hidden="true"></i>';
$strFail = '<i class="fa fa-fw fa-exclamation-circle row-icon" aria-hidden="true"></i>';

 ?>
<html>
<head>
    <title>Server Requirements</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
    <link href='https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' rel='stylesheet'>
    <style>
        body {
            font-family: 'Roboto';
            margin: 0;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            -moz-font-feature-settings: "liga" on; }

        fieldset button {
            margin-bottom: 0; }
        fieldset input[type=submit] {
            margin-bottom: 0; }

        .master {
            background-color: #f7f7f7 !important;
            min-height: 100vh;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-justify-content: center;
            -ms-flex-pack: center;
            justify-content: center;
            -webkit-align-items: center;
            -ms-flex-align: center;
            align-items: center; }

        .box {
            width: 600px;
            border-radius: 0 0 3px 3px;
            overflow: hidden;
            box-sizing: border-box;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.19), 0 3px 3px rgba(0, 0, 0, 0.23); }

        .header {
            background-color: #4a97fd;
            padding: 30px 30px 40px;
            border-radius: 3px 3px 0 0;
            text-align: center; }

        .header__title {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            color: #fff;
            font-weight: 400;
            font-size: 30px;
            margin: 0 0 15px;
        }
        .main {
            margin-top: -20px;
            background-color: #fff;
            border-radius: 0 0 3px 3px;
            padding: 40px 40px 30px; }

        .list {
            padding-left: 0;
            list-style: none;
            margin-bottom: 0;
            margin: 20px 0 35px;
            border: 1px solid rgba(0, 0, 0, 0.12);
            border-radius: 2px; }
        .list .list__item.list__title {
            background: rgba(0, 0, 0, 0.12); }
        .list .list__item.list__title.success span {
            color: #008000; }
        .list .list__item.list__title.success .fa:before {
            color: #008000; }
        .list .list__item.list__title.error span {
            color: #ff0000; }
        .list .list__item.list__title.error .fa:before {
            color: #ff0000; }

        .list__item {
            position: relative;
            overflow: hidden;
            padding: 10px 20px;
            font-size: 0.9rem;
            font-weight: 400;
            border-bottom: 1px solid rgba(0, 0, 0, 0.12);
            color: gray; }

        .list__item:first-letter {
            text-transform: uppercase; }
        .list__item:last-child {
            border-bottom: none; }
        .list__item .fa.row-icon:before {
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-justify-content: center;
            -ms-flex-pack: center;
            justify-content: center;
            -webkit-align-items: center;
            -ms-flex-align: center;
            align-items: center;
            padding: 7px 20px;
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0; }
        .list__item.success .fa:before {
            color: #2ecc71; }
        .list__item.error .fa:before {
            color: #e74c3c; }


        .float-left {
            float: left; }

        .float-right {
            float: right; }
        .fa-exclamation-circle:before{
            color: red !important;
        }

    </style>

</head>
<body>
<div class="master">
    <div class="box">
        <div class="header">
            <h1 class="header__title">    <i class="fa fa-list-ul fa-fw" aria-hidden="true"></i>
                Server Requirements
            </h1>
        </div>
        <div class="main">

            <ul class="list">

                <li class="list__item success">
                    Php (version <?php echo $requirements["php_version"]; ?> required) <span class="float-right"><?php echo phpversion(); ?> <?php echo  ($requirements["php_version"]) ?  $strOk :  $strFail; ?> </span>
                </li>


                <li class="list__item success">
                    Openssl <?php echo ($requirements["openssl"]) ?  $strOk :  $strFail; ?>
                </li>
                <li class="list__item success">
                    PDO <?php echo ($requirements["pdo"]) ?  $strOk :  $strFail; ?>
                </li>

                <li class="list__item success">
                    Mbstring <?php echo ($requirements["mbstring"]) ?  $strOk :  $strFail; ?>
                </li>

                <li class="list__item success">
                    Tokenizer <?php echo ($requirements["tokenizer"]) ?  $strOk :  $strFail; ?>
                </li>

                <li class="list__item success">
                    JSON <?php echo ($requirements["json"]) ?  $strOk :  $strFail; ?>
                </li>

                <li class="list__item success">
                    Ctype <?php echo ($requirements["ctype"]) ?  $strOk :  $strFail; ?>
                </li>

                <li class="list__item success">
                    XML <?php echo ($requirements["xml"]) ?  $strOk :  $strFail; ?>
                </li>

                <li class="list__item success">
                    CURL <?php echo ($requirements["curl"]) ?  $strOk :  $strFail; ?>
                </li>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
