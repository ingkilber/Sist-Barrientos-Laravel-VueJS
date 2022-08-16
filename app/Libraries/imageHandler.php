<?php
namespace App\Libraries;

class imageHandler
{
    public function imageUpload($image,$namePrefix,$destination){

        list($type, $file) = explode(';',$image);
        list(, $extension) = explode('/', $type);
        list(, $file) = explode(',', $file);
        $fileNameToStore = $namePrefix.rand(1, 100000000).'.'.$extension;
        $source = fopen($image, 'r');
        $destination = fopen($destination . $fileNameToStore, 'w');
        stream_copy_to_stream($source, $destination);
        fclose($source);
        fclose($destination);

        return $fileNameToStore;
    }

    public function imageUploadFixedName($image,$fileName,$destination){

        list($type, $file) = explode(';',$image);
        list(, $extension) = explode('/', $type);
        $source = fopen($image, 'r');
        $destination = fopen($destination . $fileName, 'w');
        stream_copy_to_stream($source, $destination);
        fclose($source);
        fclose($destination);
    }
}