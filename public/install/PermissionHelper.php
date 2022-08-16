<?php
include 'PathHelper.php';

class PermissionHelper
{
    protected $results = [];

    protected $config = [];

    protected $path;

    public function __construct($config = [])
    {
        $this->results['permissions'] = [];

        $this->results['errors'] = null;

        $this->config = $config;

        $this->path = PathHelper::new();
    }


    public function check(array $permissions = [])
    {
        $permissions = count($permissions) ? $permissions: $this->config->permissions;
        $permissions = (array) $permissions;
        foreach ($permissions as $folder => $permission) {
            if (! ($this->getPermission($folder))) {
                $this->addFileAndSetErrors($folder, $permission, false);
            } else {
                $this->addFile($folder, $permission, true);
            }
        }

        return $this->results;
    }

    public function createTestFile($folder)
    {
        try {
            $file = fopen($this->path->getBasepath().$folder.'test.txt', 'w');
            fwrite($file, "John Doe\n");
            fclose($file);
            $exist = file_exists($this->path->getBasepath().$folder.'test.txt');
            $this->deleteTestFile($this->path->getBasepath().$folder.'test.txt');
            return $exist;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function deleteTestFile($file_path)
    {
        unlink($file_path);
    }

    public function testFile($file_path)
    {
        try {
            $fp = fopen($this->path->getBasepath().$file_path, 'a');
            fwrite($fp, 'TEST=TEST');
            fclose($fp);
            file_put_contents($this->path->getBasepath().$file_path, str_replace(
                'TEST=TEST', '', file_get_contents($this->path->getBasepath().$file_path)
            ));
            return file_exists($this->path->getBasepath().$file_path);
        }catch (\Exception $exception) {
            return false;
        }
    }

    private function getPermission($folder)
    {
        if (is_dir($this->path->getBasepath().$folder)) {
            return $this->createTestFile($folder);
        }else {
            return $this->testFile($folder);
        }
    }



    private function addFile($folder, $permission, $isSet)
    {
        array_push($this->results['permissions'], [
            'folder' => $folder,
            'permission' => $permission,
            'isSet' => $isSet,
        ]);
    }

    private function addFileAndSetErrors($folder, $permission, $isSet)
    {
        $this->addFile($folder, $permission, $isSet);

        $this->results['errors'] = true;
    }

    public function isSupported()
    {
        $this->check();

        return !$this->results['errors'];
    }
}
