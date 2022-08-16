<?php
include 'Requirement.php';
include 'PermissionHelper.php';

function getResult() {
    $config = json_decode(file_get_contents(__DIR__ . '/config.json'));

    $requirement = new Requirement($config);

    $requirement_result = $requirement->check();

    $permissions = new PermissionHelper($config);

    $permission_result = $permissions->check();

    return array_merge(array_merge(array_merge([
        'php' => $requirement->checkPhpVersion()
    ], $requirement_result), [
        'permissions' => $permission_result
    ] ), [
        'is_supported' => $requirement->isSupported() && $permissions->isSupported()
    ]);

}
