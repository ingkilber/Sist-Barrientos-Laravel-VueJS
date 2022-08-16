<?php


namespace App\Setup\Manager;


use App\Setup\Helper\RequestSender;
use App\Setup\Helper\UrlHelper;
use App\Setup\Validator\PurchaseCodeValidator;

class UpdateManager
{
    use UrlHelper;

    protected $purchaseCodeManager;
    protected $purchaseCodeValidator;
    protected $request;
    protected $downloadManager;

    public function __construct(
        PurchaseCodeManager $purchaseCodeManager,
        PurchaseCodeValidator $purchaseCodeValidator,
        RequestSender $request,
        DownloadManager $downloadManager
    )
    {
        $this->purchaseCodeManager = $purchaseCodeManager;
        $this->purchaseCodeValidator = $purchaseCodeValidator;
        $this->request = $request;
        $this->downloadManager = $downloadManager;
    }

    public function updates()
    {
        if ($code = $this->purchaseCodeManager->getCode()) {
            $result = $this->purchaseCodeValidator->validate($code);
            if ($result && $result->data == 'Verified') {
                $url = $this->url($code, 'update/list');
                $result = $this->request->get($url)->data;
                $this->downloadManager->download($result, $code);
                return (object)['status' => true, 'result' => $result];
            }
        }
        return (object)['status' => false, 'message' => trans('default.invalid_purchase_code')];
    }
}
