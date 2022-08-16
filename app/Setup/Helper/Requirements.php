<?php


namespace App\Setup\Helper;


class Requirements
{
    protected $results = [];

    public function getPhpVersion()
    {
        $version = PHP_VERSION;
        preg_match("#^\d+(\.\d+)*#", $version, $filtered);
        $currentVersion = $filtered[0];

        return [
            'full' => $version,
            'version' => $currentVersion,
        ];
    }

    /**
     * Check PHP version requirement.
     *
     * @param string|null $minPhpVersion
     * @return array
     */
    public function checkPhpVersion(string $minPhpVersion = null)
    {
        $minVersionPhp = $minPhpVersion;
        $currentPhpVersion = $this->getPhpVersion();
        $supported = false;

        if ($minPhpVersion == null) {
            $minVersionPhp = $this->getMinPhpVersion();
        }

        if (version_compare($currentPhpVersion['version'], $minVersionPhp) >= 0) {
            $supported = true;
        }

        $phpStatus = [
            'full' => $currentPhpVersion['full'],
            'current' => $currentPhpVersion['version'],
            'minimum' => $minVersionPhp,
            'supported' => $supported,
        ];

        return $phpStatus;
    }

    public function getMinPhpVersion()
    {
        return config('installer.core.minPhpVersion');
    }

    public function check(array $requirements = [])
    {
        $requirements = count($requirements) ? $requirements : config('installer.requirements');

        foreach ($requirements as $type => $requirement) {
            switch ($type) {
                // check php requirements
                case 'php':
                    $this->checkPhpRequirments($requirements, $type);
                    break;
                // check apache requirements
                case 'apache':
                    $this->checkApacheRequirements($requirements, $type);
                    break;
            }
        }
        return $this->results;
    }

    public function checkPhpRequirments($requirements, $type)
    {
        foreach ($requirements[$type] as $requirement) {
            $this->results['requirements'][$type][$requirement] = true;

            if (! extension_loaded($requirement)) {
                $this->results['requirements'][$type][$requirement] = false;

                $this->results['errors'] = true;
            }
        }
        return $this->results;
    }

    public function checkApacheRequirements($requirements, $type)
    {
        foreach ($requirements[$type] as $requirement) {
            // if function doesn't exist we can't check apache modules
            if (function_exists('apache_get_modules')) {
                $this->results['requirements'][$type][$requirement] = true;

                if (! in_array($requirement, apache_get_modules())) {
                    $this->results['requirements'][$type][$requirement] = false;

                    $this->results['errors'] = true;
                }
            }
        }
        return $this->results;
    }

    public function isSupported()
    {
        if ($this->checkPhpVersion()['supported']) {
            $requirements = collect($this->check()['requirements']['php'])->filter(function ($r) {
                return !$r;
            });
            return !$requirements->count();
        }
        return false;
    }
}
