<?php

$config = json_decode(file_get_contents(__DIR__ . '/install/config.json'));

$required_php_version = $config->core->min_php_version;

$requirements = array(
    "php_version" => ($required_php_version <= phpversion()) ? $required_php_version : false
);

$required_extensions = $config->requirements->php;

foreach ($required_extensions as $extension){
    $requirements[$extension] = extension_loaded($extension);
}

return $requirements;
