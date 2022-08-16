<?php

namespace App\Libraries;


use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Config;

class AllSettingFormat
{

    public function getDateFormat()
    {
        $dateFormat = Config::get('date_format');

        if ($dateFormat == 'd/m/Y') return 'dd/MM/yyyy';
        if ($dateFormat == 'm/d/Y') return 'MM/dd/yyyy';
        if ($dateFormat == 'Y/m/d') return 'yyyy/MM/dd';

        if ($dateFormat == 'd-m-Y') return 'dd-MM-yyyy';
        if ($dateFormat == 'm-d-Y') return 'MM-dd-yyyy';
        if ($dateFormat == 'Y-m-d') return 'yyyy-MM-dd';

        if ($dateFormat == 'd.m.Y') return 'dd.MM.yyyy';
        if ($dateFormat == 'm.d.Y') return 'MM.dd.yyyy';
        if ($dateFormat == 'Y.m.d') return 'yyyy.MM.dd';
    }

    public function getDateRangeFormat()
    {
        $dateFormat = Config::get('date_format');

        if ($dateFormat == 'd/m/Y') return 'DD/MM/YYYY';
        if ($dateFormat == 'm/d/Y') return 'MM/DD/YYYY';
        if ($dateFormat == 'Y/m/d') return 'YYYY/MM/DD';

        if ($dateFormat == 'd-m-Y') return 'DD-MM-YYYY';
        if ($dateFormat == 'm-d-Y') return 'MM-DD-YYYY';
        if ($dateFormat == 'Y-m-d') return 'YYYY-MM-DD';

        if ($dateFormat == 'd.m.Y') return 'DD.MM.YYYY';
        if ($dateFormat == 'm.d.Y') return 'MM.DD.YYYY';
        if ($dateFormat == 'Y.m.d') return 'YYYY.MM.DD';
    }

    public function getDate($date)
    {
        $dateFormat = Config::get('date_format');

        return date($dateFormat, strtotime($date));
    }

    public function getCurrency($currencyBill)
    {
        $format = Config::get('currency_format');
        $symbol = Config::get('currency_symbol');

        if ($format && $symbol) {
            if ($format == 'left') {
                return $symbol . $currencyBill;
            } else if ($format == 'left-space') {
                return $symbol . " " . $currencyBill;
            } else if ($format == 'right-space') {
                return $currencyBill . " " . $symbol;
            } else {
                return $currencyBill . $symbol;
            }
        } else {
            return $currencyBill;
        }
    }

    public function thousandSep($val)
    {
        $format = Config::get('thousand_separator');
        $sep = Config::get('decimal_separator');
        $numDec = Config::get('number_of_decimal');

        if ($format == "space") $format = ' ';

        if ($format || $sep || $numDec) {
            return number_format($val, (int)$numDec, $sep, $format);
        } else {
            return $val;
        }
    }

    public function timeFormat($time)
    {

        $timeFormat = Config::get('time_format');
        if ($timeFormat == '12h') {
            if (is_array($time)) {
                $timeArray = [];
                foreach ($time as $t) {
                    array_push($timeArray, date("g:i a", strtotime($t)));
                }
                return $timeArray;
            } else {
                return date("g:i a", strtotime($time));
            }
        } else {
            return date("H:i:s", strtotime($time));
        }
    }

    public function setTimeFormat($time)
    {

        if (is_array($time)) {
            $timeArray = [];
            foreach ($time as $t) {
                array_push($timeArray, date("H:i:s", strtotime($t)));
            }
            return $timeArray;
        } else {
            return date("H:i:s", strtotime($time));
        }

    }

    public function getInvoiceFixes()
    {
        $fixes = [
            'prefix' => Config::get('invoice_prefix'),
            'suffix' => Config::get('invoice_suffix'),
            'lastInvoiceNumber' => Config::get('last_invoice_number'),
            'purchasePrefix' => Config::get('purchase_invoice_prefix'),
            'purchaseSuffix' => Config::get('purchase_invoice_suffix'),
            'purchaseLastInvoiceNumber' => Config::get('purchase_last_invoice_number'),
        ];
        return $fixes;
    }

    public function getCurrentBranch()
    {
        $authID = Auth::user('id')->id;
        $currentBranch = Setting::select('setting_value')->where('setting_name', 'current_branch')->where('user_id', $authID)->first();
        if ($currentBranch) {
            $branch = Branch::select('id', 'name', 'branch_type', 'is_cash_register', 'is_shipment')->where('id', $currentBranch->setting_value)->first();

            return $branch;
        } else {
            return null;
        }
    }

    public function getCurrencySeparator($value)
    {
        return $this->getCurrency($this->thousandSep($value));
    }

    public function getShortcutStatus()
    {
        return Setting::select('setting_value')->where('setting_name', 'shortcut_status')->first();
    }
}