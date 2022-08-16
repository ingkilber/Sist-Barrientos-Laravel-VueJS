<?php

namespace App\Libraries;


use App\Models\Branch;
use App\Models\ProductAttribute;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\Setting;
use App\Models\Tax;
use Illuminate\Support\Facades\Auth;

/**
 * @property  getLanguage
 */
class ProductData
{
    public function productSupportingData()
    {
        $brands = ProductBrand::allData();
        $categories = ProductCategory::allData();
        $groups = ProductGroup::allData();
        $taxes = Tax::allData();
        $branches = Branch::allData();
        $units = ProductUnit::allData();
        $attributes = ProductAttribute::allData();
        $defaultReorder = Setting::getSettingValue('re_order')->setting_value;

        return ['units' => $units, 'brands' => $brands, 'categories' => $categories, 'groups' => $groups, 'taxes' => $taxes, 'branches' => $branches, 'attributes' => $attributes, 'defaultReorder' => $defaultReorder];
    }

}