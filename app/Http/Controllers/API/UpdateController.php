<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use File;
use Illuminate\Support\Facades\Lang;
use ZipArchive;
use Illuminate\Filesystem\Filesystem;


class UpdateController extends Controller
{
    public function checkPurchaseKey()
    {
        $purchaseKey = Setting::getSettingValue('purchase_code')->setting_value;

        if (empty($purchaseKey)){
            return ['isPurchased'=> false];
        }else{
            return ['isPurchased'=> true];
        }
    }

    public function applicationVersion()
    {
        return config('gain');
    }

    public function updateAppUrl()
    {
        return config('gain.update_url');
    }


    public function checkPurchaseCode()
    {
        if (Setting::query()->where('setting_name', '=', 'purchase_code')->exists()) {
            $purchase_code = Setting::where('setting_name', 'purchase_code')->select('setting_value')->first()->setting_value;

        } else {
            $purchase_code = "";
        }

        return $purchase_code;
    }

    public function crulRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = json_decode(curl_exec($ch), true);
        $err = curl_error($ch);
        $errno = curl_errno($ch);
        $error_message = curl_strerror($errno);
        // Closing
        curl_close($ch);

        if ($err) {

            return "cURL Error #:" . $errno . ' - ' . $error_message;

        } else {

            return $result;
        }
    }

    public function downloadFilesFromServer($result)
    {
        ini_set('memory_limit', '256M');
        set_time_limit( 300);

        if (phpversion() >= 7.3) {
            $domain_name = $_SERVER['HTTP_HOST'];
            $applicationDetails = $this->applicationVersion();

            if (gettype($result) == 'array') {
                $tempResult = $result;

                foreach ($tempResult as &$versionName) {

                    $versionName['version'] = $versionName['version'] . '.zip';
                }

                $File = new Filesystem;

                if (!$File->isDirectory(public_path('updates'))) {
                    $File->makeDirectory(public_path('updates'), 0755);
                }

                $filesInFolder = $File->allFiles(public_path('updates' . DIRECTORY_SEPARATOR));
                $downloadFileList = $result;

                foreach ($filesInFolder as $filePath) {

                    $savedVersionZip = substr($filePath, strrpos(public_path('updates' . DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR) + 1, strlen($filePath) - 1);
                    $spliceIndex = array_search($savedVersionZip, array_column($tempResult, 'version'));

                    if ($spliceIndex == true) {
                        unset($downloadFileList[$spliceIndex]);
                    }
                }

                if ($downloadFileList) {

                    foreach ($downloadFileList as $downloadFile) {

                        $url = $applicationDetails['update_url'] . "/update/download/" . $applicationDetails['app_id'] . "/" . $downloadFile['version'] . "?domain_name=" . $domain_name . "&purchase_key=" . $this->checkPurchaseCode() . "&app_version=" . $applicationDetails['app_version'];
                        $destination = public_path('updates' . DIRECTORY_SEPARATOR);
                        set_time_limit(0);
                        $filePath = fopen($destination . $downloadFile['version'] . '.zip', 'w+');
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
                        curl_setopt($ch, CURLOPT_FILE, $filePath);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_exec($ch);
                        curl_close($ch);
                        fclose($filePath);
                    }

                } else {

                    return response()->json('No updates available', 302);
                }

            } else {

                return $result;
            }

        } else {
            return response()->json('Please upgrade your php version >= 7.3', 404);
        }
    }

    public function curl_get_contents()
    {

        if ($this->checkPurchaseCode()) {
            $domain_name = $_SERVER['HTTP_HOST'];
            $applicationDetails = $this->applicationVersion();
            $url = $applicationDetails['update_url'] . "/verification/" . $applicationDetails['app_id'] . "?domain_name=" . $domain_name . "&purchase_key=" . $this->checkPurchaseCode() . "&app_version=" . $applicationDetails['app_version'];

            if ($this->crulRequest($url)['data'] == 'Verified') {
                $url = $applicationDetails['update_url'] . "/update/list/" . $applicationDetails['app_id'] . "?domain_name=" . $domain_name . "&purchase_key=" . $this->checkPurchaseCode() . "&app_version=" . $applicationDetails['app_version'];
                $result = $this->crulRequest($url)['data'];
                $this->downloadFilesFromServer($result);

                return $result;

            } else {
                $response = [
                    'message' => Lang::get('lang.' . $this->crulRequest($url)['data']),
                ];

                return response()->json($response, 404);
            }

        } else {
            $response = [
                'message' => Lang::get('lang.invalid_purchase_code'),
            ];

            return response()->json($response, 404);
        }
    }


    public function versionUpdateList()
    {
        $updateList = $this->curl_get_contents();
        $applicationDetails = $this->applicationVersion();

        if (is_array($updateList)) {
            $spliceIndex = array_search($applicationDetails['app_version'], array_column($updateList, 'version'));

            if ($spliceIndex == false) {

                return $updateList;

            } else {

                for ($i = 0; $i <= $spliceIndex; $i++) {

                    unset($updateList[$i]);
                }

                return $updateList;
            }
        } else {
            $response = [
                'message' => Lang::get('lang.no_updates_found'),
            ];

            return response()->json($response, 404);
        }

    }

    public function nexInstallVersion($version)
    {
        $updateList = $this->versionUpdateList();
        $checkInstallable = array_search($version, array_column($updateList, 'version'));

        if ($checkInstallable === 0) {
            $data = ['version' => $updateList[$checkInstallable]['version'], 'check' => true];

            return $data;

        } else {
            $data = ['version' => $updateList[0]['version'], 'check' => false];
            return $data;
        }
    }

    public function updateAction($version)
    {
        $File = new Filesystem;
        $filePath =  base_path() . DIRECTORY_SEPARATOR . 'bootstrap'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'packages.php';
        $filePath2 =  base_path() . DIRECTORY_SEPARATOR . 'bootstrap'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'services.php';


        if ($File->exists($filePath) && $File->exists($filePath2)) {

            unlink($filePath);
            unlink($filePath2);
        }


        ini_set('memory_limit', '256M');
        set_time_limit( 300);
        if (phpversion() >= 7.3) {
            $extensionsCheck = array_search('zip', get_loaded_extensions());


            if ($extensionsCheck == false) {

                $response = [
                    'message' => Lang::get('lang.install_zip_extension'),
                ];

                return response()->json($response, 404);

            } else {

                if ($this->nexInstallVersion($version)['check'] == true) {
                    $basePath = base_path();
                    $publicPath = public_path();
                    $appPath = app_path();
                    $zip = new ZipArchive;
                    $zipFile = $publicPath . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . $version . '.zip';
                    $zipOpen = $zip->open($zipFile);
                    $File = new Filesystem;
                    $path = 'updates' . DIRECTORY_SEPARATOR . 'temp';
                    $executableFile = '';

                    if ($File->exists($publicPath . DIRECTORY_SEPARATOR . $path)) {
                        $File->deleteDirectory($publicPath . DIRECTORY_SEPARATOR . $path);

                    } else {
                        $File->makeDirectory($publicPath . DIRECTORY_SEPARATOR . $path);
                    }

                    if ($zipOpen === TRUE) {
                        $zip->extractTo($publicPath . DIRECTORY_SEPARATOR . $path);
                        $zip->close();
                        $filesInFolder = $File->allFiles($publicPath . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $version);

                        foreach ($filesInFolder as $filePath) {

                            $pastePath = substr($filePath, strlen($publicPath . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $version));
                            $rootDir = substr($basePath . $pastePath, 0, strrpos($basePath . $pastePath, DIRECTORY_SEPARATOR));

                            if (!$File->exists($rootDir)) {
                                $File->makeDirectory($rootDir, 0777, true, true);
                            }

                            if (strpos($filePath, 'execute.php') != false) {

                                if ($File->exists($appPath . DIRECTORY_SEPARATOR . 'Execute')) {
                                    $File->deleteDirectory($appPath . DIRECTORY_SEPARATOR . 'Execute');
                                }

                                $File->makeDirectory($appPath . DIRECTORY_SEPARATOR . 'Execute');
                                $File->move($filePath, $appPath . DIRECTORY_SEPARATOR . 'Execute' . DIRECTORY_SEPARATOR . 'execute.php');
                                $executableFile = true;

                            } elseif (strpos($filePath, $version . '.sql') != false) {

                            } elseif (strpos($filePath, 'gain.php') != false) {
                                copy($filePath, $basePath . $pastePath);

                            } else {
                                copy($filePath, $basePath . $pastePath);
                            }
                        }

                        //used to move gain.php file

                        $filePath = public_path($path . DIRECTORY_SEPARATOR . $version . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'gain.php');

                        if ($File->exists($filePath)) {
                            $File->move($filePath, base_path('config' . DIRECTORY_SEPARATOR . 'gain.php'));

                        }

                        if ($executableFile === true) {
                            $execute = new \App\Execute\execute;
                            $execute->executeOperation($path, $version);
                            $File->deleteDirectory($appPath . DIRECTORY_SEPARATOR . 'Execute');
                        }

                        $File->deleteDirectory($publicPath . DIRECTORY_SEPARATOR . $path);

                        $response = [
                            'message' => Lang::get('lang.version') . $version . Lang::get('lang.installed_successfully'),
                        ];

                        return response()->json($response, 201);

                    } else {
                        $response = [
                            'message' => Lang::get('lang.something_went_wrong'),
                        ];

                        return response()->json($response, 401);
                    }

                } else {
                    $response = [
                        'message' => Lang::get('lang.please_install_version') . $this->nexInstallVersion($version)['version'] . Lang::get('lang.first'),
                    ];

                    return response()->json($response, 404);
                }
            }

        } else {
            $response = [
                'message' => 'Please upgrade php version to 7.3+',
            ];

            return response()->json($response, 404);
        }
    }
}