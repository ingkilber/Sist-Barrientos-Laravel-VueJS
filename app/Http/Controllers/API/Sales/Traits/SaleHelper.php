<?php


namespace App\Http\Controllers\API\Sales\Traits;


use App\Libraries\searchHelper;
use App\Models\Setting;
use App\Models\ShortcutKey;
use Illuminate\Http\Request;

trait SaleHelper
{
    public function optionShaper(Request $request)
    {
        $options['fields'] = 0;
        $options['limit'] = $request->rowLimit;
        $options['offset'] = $request->offset;
        $options['searchValue'] = searchHelper::inputSearch($request->searchValue);
        $options['branchId'] = $request->currentBranch;
        $options['orderType'] = $request->orderType;
        $options['onlyInStockProducts'] = false;

        if ($options['orderType'] == "sales")
            $options['onlyInStockProducts'] = Setting::getOneSetting("out_of_stock_products")->setting_value;

        return $options;
    }

    public function getShortcutSettings()
    {
        $allKeyboardShortcut = ShortcutKey::allData();
        return ['allKeyboardShortcut' => $allKeyboardShortcut];
    }
}