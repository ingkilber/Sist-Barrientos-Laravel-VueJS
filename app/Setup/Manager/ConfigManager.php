<?php


namespace App\Setup\Manager;


class ConfigManager
{
    public function set($key = 'installed', $value = true)
    {
        $configs = config('gain');

        $configs[$key] = $value;

        $configs = var_export($configs, 1);

        file_put_contents(config_path('gain.php'), "<?php\n return $configs ;");

        return true;
    }
}