<?php

namespace App\Http\Controllers\API\Sales;

use App\Http\Controllers\API\Sales\Traits\SaleHelper;
use App\Http\Controllers\API\SalesController;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class SaleApiController extends Controller
{
    use SaleHelper;

    public function index($limit, $offset, Request $request)
    {
        $shortcutSettings = (new SalesController())->getShortcutSettings();
        $options = $this->optionShaper($request);

        $options['limit'] = $limit;
        $options['offset'] = $offset;
        $products = Product::getAllProducts($options);
        $productVariants = Product::getProductVariantsList(
            $options['branchId'],
            $options['orderType'],
            $options['onlyInStockProducts'],
            $products
        );

        return [
            'products' => $products,
            'count' => $products->count(),
            'barcodeResultValue' => null,
            'shortcutSettings' => $shortcutSettings,
            'variants' => $productVariants
        ];
    }

}
