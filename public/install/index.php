<?php
include 'getResult.php';

$result = getResult();
$is_supported = $result['is_supported'];

if ($is_supported) {
    $sub_folder = str_replace('index.php', '', $_SERVER['PHP_SELF']);
    $sub_folder = str_replace('install', '', $sub_folder);
    $base_url = rtrim($sub_folder, '/');
    $base_url = $base_url ? $base_url.'/' : '/';
    echo '<script>window.location = "'.$base_url.'app/environment"</script>';
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <link rel="stylesheet" type="text/css" href="../css/app.css" />
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
</head>
<body>

<div id="app">
    <div class="p-3">
        <div class="row justify-content-center">
            <div class="col-sm-6">
                <div class="card border-0 shadow p-primary">
                    <div class="card-header bg-dark text-white border-0 p-4">
                        <h4 class="text-center text-capitalize mb-0">
                            Required Environments
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-capitalize py-2">
                            PHP
                            <span class="font-size-90">
                                Version <?php echo $result['php']['minimum'] ?> required
                            </span>
                            <div class="float-right">
                                <?php
                                $class = $result['php']['supported'] ? 'text-success': 'text-danger'
                                ?>
                                <span class="<?php echo $class ?>">
                                    <?php echo $result['php']['current'] ?>
                                </span>
                            </div>
                        </div>
                        <div>
                            <?php
                            $php_requirements = $result['requirements']['php'];

                            $requirements = array_filter(array_keys($php_requirements), function ($requirement) use ($php_requirements) {
                                return !$php_requirements[$requirement];
                            });

                            if (count($requirements)) {
                            ?>
                            <ul class="list-group" >
                                <?php
                                foreach ($php_requirements as $key => $requirement) {
                                    ?>
                                    <li class="border-0 list-group-item d-flex justify-content-between align-items-center px-0" >
                                        <?php echo ucfirst($key) ?>
                                        <?php if(!$requirement){ ?>
                                            <span class="text-danger">&times;</span>
                                        <?php } ?>
                                    </li>
                                <?php } ?>
                            </ul>
                            <?php
                            }
                            ?>
                            <br/>
                            <?php
                            $permissions_required = $result['permissions']['permissions'];

                            $permissions = array_filter($permissions_required, function ($permission) {
                                return !$permission['isSet'];
                            });

                            $permissions = array_map(function ($permission) {
                                return $permission['folder'];
                            }, $permissions);

                            $string = implode(', ', $permissions);

                            if (count($permissions)) {
                                ?>
                                <div class="note-title d-flex">
                                    <span class="fa fa-book-open"></span>
                                    <h6 class="card-title pl-2">Attention </h6>
                                </div>

                                <div class="note note-warning p-4">
                                    <p class="m-1 text-muted"><b><?php echo $string ?></b> from <b>root</b> directory required server write permission to install and run the apps. You can remove write permission of <b>.env</b> after installation.</p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
