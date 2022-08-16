<?php /** @noinspection PhpComposerExtensionStubsInspection */


namespace App\Setup\Manager;


use App\Exceptions\GeneralException;
use Illuminate\Filesystem\Filesystem;
use ZipArchive;

class FileManager
{
    protected $file;

    protected $separator = DIRECTORY_SEPARATOR;

    protected $zip;

    public function __construct(Filesystem $file, ZipArchive $zipArchive)
    {
        $this->file = $file;
        $this->zip = $zipArchive;
    }

    public function removeCachedFile()
    {
        $filePath =  base_path().$this->separator.'bootstrap'.$this->separator.'cache'.$this->separator.'packages.php';
        $filePath2 =  base_path().$this->separator.'bootstrap'.$this->separator.'cache'.$this->separator.'services.php';

        if ($this->file->exists($filePath) && $this->file->exists($filePath2)) {
            unlink($filePath);
            unlink($filePath2);
        }

        return $this;
    }

    public function extract($version)
    {
        $zipFile = public_path().$this->separator.'updates'.$this->separator.$version.'.zip';
        $zipOpen = $this->zip->open($zipFile);
        $path = 'updates'.$this->separator.'temp';

        $executableFile = '';
        if ($this->file->exists(public_path().$this->separator.$path)) {
            $this->file->deleteDirectory(public_path().$this->separator. $path);
        } else {
            $this->file->makeDirectory(public_path().$this->separator. $path);
        }

        throw_if($zipOpen !== TRUE, new GeneralException(trans('default.something_went_wrong'), 401));

        $this->zip->extractTo(public_path().$this->separator.$path);

        $this->zip->close();

        $all_downloaded_files = $this->file->allFiles(public_path().$this->separator.$path.$this->separator.$version);
        foreach ($all_downloaded_files as $file_path) {
            $paste_path = substr($file_path, strlen(public_path().$this->separator.$path.$this->separator.$version));
            $root_dir = substr(base_path().$paste_path, 0, strrpos(base_path().$paste_path, $this->separator));

            if (!$this->file->exists($root_dir))
                $this->file->makeDirectory($root_dir, 0777, true, true);

            $exploded = explode(DIRECTORY_SEPARATOR, $file_path->getRelativePath());

            if (strpos($file_path, 'execute.php')) {
                if ($this->file->exists(app_path().$this->separator.'Execute')) {
                    $this->file->deleteDirectory(app_path().$this->separator.'Execute');
                }
                $this->file->makeDirectory(app_path().$this->separator.'Execute');
                $this->file->move($file_path, app_path().$this->separator.'Execute'.$this->separator.'execute.php');
                $executableFile = true;
            } elseif (strpos($file_path, $version . '.sql') != false) {

            } elseif (!strpos($file_path, 'gain.php')) {
                if (in_array('public', $exploded) && $file_path->getFilename() != 'mix-manifest.json') {
                    if ($this->file->exists($file_path)) {
                        $base_path = str_replace('/src', '', base_path());
                        $this->file->copy($file_path, $base_path.str_replace('public/', '', $paste_path));
                    }
                }else {
                    copy($file_path, base_path() . $paste_path);
                }
            } else {
                copy($file_path, base_path() . $paste_path);
            }
        }
        //used to move gain.php file
        $file_path = public_path($path.$this->separator.$version.$this->separator.'config'.$this->separator.'gain.php');
        if ($this->file->exists($file_path)) {
            $this->file->move($file_path, base_path('config'.$this->separator.'gain.php'));
        }
        if ($executableFile) {
            $execute = new \App\Execute\execute;
            $execute->executeOperation($path, $version);
            $this->file->deleteDirectory(app_path().$this->separator. 'Execute');
        }
        $this->file->deleteDirectory(public_path() .$this->separator. $path);

        return true;
    }
}
