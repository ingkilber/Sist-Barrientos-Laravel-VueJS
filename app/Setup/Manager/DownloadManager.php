<?php /** @noinspection PhpComposerExtensionStubsInspection */


namespace App\Setup\Manager;


use App\Exceptions\GeneralException;
use App\Helpers\Traits\SetIiiTrait;
use App\Setup\Helper\RequestSender;
use App\Setup\Helper\UrlHelper;
use App\Setup\Helper\ValidatePHPVersion;
use Illuminate\Filesystem\Filesystem;

class DownloadManager
{
    use UrlHelper, SetIiiTrait, ValidatePHPVersion;

    protected $request;

    public function __construct(RequestSender $request)
    {
        $this->request = $request;
    }

    public function download($result, $code)
    {
        $this->setMemoryLimit();
        $this->setExecutionTime();

        $this->validatePHPVersion();

        $separator = DIRECTORY_SEPARATOR;

        if (!is_array($result))
            return $result;

        $results = collect((array)$result)->map(function ($version) {
            $version->version = $version->version.'.zip';
            return $version;
        })->toArray();

        $file = new Filesystem();

        if (!$file->isDirectory(public_path('updates')))
            $file->makeDirectory(public_path('updates'));

        $downloaded_updates = $file->allFiles(public_path('updates' . $separator));

        //start from here
        $pending_download_list = $result;
        foreach ($downloaded_updates as $filePath) {
            $saved_version = substr($filePath,strrpos(public_path('updates' . $separator), $separator) + 1, strlen($filePath) - 1 );
            $spliceIndex = array_search($saved_version, array_column($results, 'version'));
            if ($spliceIndex)
                unset($pending_download_list[$spliceIndex]);
        }

        throw_if(
            !(is_array($pending_download_list) && count($pending_download_list)),
            new GeneralException(json_encode([
                'status' => false,
                'version' => config('gain.app_version'),
                'message' => trans('default.no_updates_found'),
                'message_1' => trans('default.You are using version', ['version' => config('gain.app_version')]),
                'message_2' => trans('default.no_new_update_found')
            ]))
        );

        foreach ($pending_download_list as $download) {
            $applicationDetails = config('gain');
            $url = config('gain.update_url') . "/update/download/" . $applicationDetails['app_id'] . "/" . str_replace('.zip', '', $download->version) . "?domain_name=" . request()->getHost() . "&purchase_key=" . $code . "&app_version=" . $applicationDetails['app_version'];

            $destination = public_path('updates'.$separator);
            $this->setExecutionTime(0);

            $filePath = fopen($destination.str_replace('.zip', '', $download->version).'.zip', 'w+');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            curl_setopt($ch, CURLOPT_FILE, $filePath);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);
            curl_close($ch);
            fclose($filePath);
        }
    }

}
