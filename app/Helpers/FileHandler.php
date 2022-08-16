<?php


namespace App\Helpers;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Exception;

trait FileHandler
{
    protected $storage_prefix = 'public';

    public function storeFile( $file, $folder = 'avatar') {

        $name = Str::random(40).".".$file->getClientOriginalExtension();
        $file->storeAs("{$this->storage_prefix}/{$folder}", $name);
        return Storage::url($folder.'/'.$name);
    }

    public function saveImage(UploadedFile $file, $subdirectory = 'logo', $height = 300) {
        try {
            $file_path = $subdirectory. '/' .uniqid().'.'.$file->getClientOriginalExtension();
            Storage::put($this->storage_prefix.'/'.$file_path, $this->makeImage($file, $height)->__toString(), 'public' );
            return (object)["success" => true, "message" => "File has been uploaded successfully", "path" => $file_path ];
        }catch (Exception $exception) {
            $file_name = uniqid().'.'.$file->getClientOriginalExtension();
            $file->storeAs($this->storage_prefix.'/'.$subdirectory, $file_name);
            return (object)["success" => true, "message" => "File has been uploaded successfully", "path" => $subdirectory.'/'.$file_name ];
        }
    }

    public function makeImage(UploadedFile $file, $height = 300) {
        return Image::make($file)->resize(null, $height, function ($constraint){
            $constraint->aspectRatio();
        })->save();
    }

    public function uploadImage(UploadedFile $uploadedFile = null, $folder = "logo", $height = 300) {
        if (is_null($uploadedFile))
            return null;
        $file = $this->saveImage($uploadedFile, $folder, $height);
        if ($file->success)
            return Storage::url($file->path);
        return null;
    }

    public function isFile(string $path = null)
    {
        return Storage::exists("{$this->storage_prefix}/{$path}");
    }

    public function deleteImage(string $path = null)
    {
        return $this->deleteFile($path);
    }

    public function removeStorage($path)
    {
        return str_replace('/storage', '', $path);
    }

    public function deleteFile(string $path = null)
    {
        $path = $this->removeStorage($path);
        if ($this->isFile($path))
            return Storage::delete("{$this->storage_prefix}/{$path}");
        return false;
    }

    public function deleteMultipleFile(array $paths)
    {
        foreach ($paths as $path) {
            $this->deleteFile($path);
        }

        return true;
    }

    public function filePath(string $path = null)
    {
        $path = $this->removeStorage($path);
        if ($this->isFile($path))
            return Storage::url("{$this->storage_prefix}/{$path}");
        return null;
    }


}
