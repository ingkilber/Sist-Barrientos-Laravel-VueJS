<?php

namespace App\Http\Controllers\View;

use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use App\Libraries\Permissions;

class NavigationController extends Controller
{
    public function permissionCheck()
    {
        return new Permissions;
    }

    public function supplierView()
    {
        return view('suppliers.SuppliersIndex');
    }

    public function customerView()
    {
        return view('customers.CustomersIndex');
    }

    public function contactView()
    {
        $tabName = '';
        $routeName = '';
        if(isset($_GET['tab_name'])){
            $tabName = $_GET['tab_name'];
        }
        if(isset($_GET['route_name'])){
            $routeName = $_GET['route_name'];
        }
        return view('contacts.ContactsIndex',['tab_name'=>$tabName, 'route_name'=>$routeName]);

    }

    public function dashboardView()
    {
        return view('dashboard');
    }

    public function productView()
    {
        $tabName = '';
        $routeName = '';

        if (isset($_GET['tab_name'])) {
            $tabName = $_GET['tab_name'];
        }

        if (isset($_GET['route_name'])) {
            $routeName = $_GET['route_name'];
        }

        return view('products.ProductsIndex', ['tab_name' => $tabName, 'route_name' => $routeName]);
    }

    public function profileView()
    {
        $user = Auth::user();
        $dataFormat = Setting::getSettingValue('date_format')->setting_value;

        if($dataFormat == "d/m/Y"){
            $user->dateformat = "dd/MM/yyyy";
        }
        if($dataFormat == "m/d/Y"){
            $user->dateformat = "MM/dd/yyyy";
        }
        if($dataFormat == "Y/m/d"){
            $user->dateformat = "yyyy/MM/dd";
        }
        if($dataFormat == "d-m-Y"){
            $user->dateformat = "dd-MM-yyyy";
        }
        if($dataFormat == "m-d-Y"){
            $user->dateformat = "MM-dd-yyyy";
        }
        if($dataFormat == "Y-m-d"){
            $user->dateformat = "yyyy-MM-dd";
        }
        if($dataFormat == "d.m.Y"){
            $user->dateformat = "dd.MM.yyyy";
        }
        if($dataFormat == "m.d.Y"){
            $user->dateformat = "MM.dd.yyyy";
        }
        if($dataFormat == "Y.m.d"){
            $user->dateformat = "yyyy.MM.dd";
        }

        return view('users.profile', ['profile' => $user]);

    }

    public function reportView()
    {
        $tabName = '';
        $routeName = '';
        if (isset($_GET['tab_name'])) {
            $tabName = $_GET['tab_name'];
        }
        if (isset($_GET['route_name'])) {
            $routeName = $_GET['route_name'];
        }

        return view('reports.ReportsIndex', ['tab_name' => $tabName, 'route_name' => $routeName]);
    }

    public function customerDetailsView($id)
    {
        $perm = new PermissionController;
        $tabName = '';
        $routeName = '';

        $permission = $perm->customerDetailsPermission();
        if ($permission) {
            $customerDetails = Customer::customerDetails($id);

            if (isset($_GET['tab_name'])) {
                $tabName = $_GET['tab_name'];
            }
            if (isset($_GET['route_name'])) {
                $routeName = $_GET['route_name'];
            }
            return view('customers.CustomerDetails', ['customerDetails' => $customerDetails, 'tab_name' => $tabName, 'route_name' => $routeName]);

        } else {

            abort(404);
        }
    }

    public function inviteView()
    {
        return view('invitation/invite');
    }

    public function productDetailsView($id)
    {
        $tabName = '';
        $routeName = '';
        if (isset($_GET['tab_name'])) {
            $tabName = $_GET['tab_name'];
        }
        if (isset($_GET['route_name'])) {
            $routeName = $_GET['route_name'];
        }
        return view('products.ProductDetails', ['id' => $id, 'tab_name' => $tabName, 'route_name' => $routeName]);
    }

    public function purchaseReportsDetailsView($id)
    {
        $tabName = "";
        $routeName = "";
        if (isset($_GET['tab_name'])) {
            $tabName = $_GET['tab_name'];
        }
        if (isset($_GET['route_name'])) {
            $routeName = $_GET['route_name'];
        }
        return view(
            'reports.receiveDetailsReports',
            [
                'id' => $id,
                'tabName' => $tabName,
                'route_name' => $routeName
            ]
        );
    }

    public function salesReportsDetailsView($id)
    {
        $tabName = "";
        $routeName = "";
        if (isset($_GET['tab_name'])) {
            $tabName = $_GET['tab_name'];
        }
        if (isset($_GET['route_name'])) {
            $routeName = $_GET['route_name'];
        }
        return view(
            'reports.SalesReportsDetails',
            [
                'id' => $id,
                'tabName' => $tabName,
                'route_name' => $routeName
            ]
        );
    }

    public function settingsView()
    {
        $tabName = '';
        $routeName = '';
        if (isset($_GET['tab_name'])) {
            $tabName = $_GET['tab_name'];
        }
        if (isset($_GET['route_name'])) {
            $routeName = $_GET['route_name'];
        }
        return view('settings.SettingsIndex', ['tab_name' => $tabName, 'route_name' => $routeName]);
    }

}
