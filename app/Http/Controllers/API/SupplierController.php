<?php

namespace App\Http\Controllers\API;

use App\Libraries\Permissions;
use App\Models\Supplier;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Libraries\imageHandler;

class SupplierController extends Controller
{
    public function permissionCheck()
    {
        return new Permissions();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required'
        ]);
        $data = [];
        $data['first_name'] = $request->input('first_name');
        $data['last_name'] = $request->input('last_name');
        $data['email'] = $request->input('email');
        $data['company'] = $request->input('company');
        $data['phone_number'] = $request->input('phone_number');
        $data['tin_number'] = $request->input('tin_number');
        $data['address'] = $request->input('address');
        $data['created_by'] = Auth::user()->id;
        $supplierId = Supplier::getInsertedId($data);

        $response = [
            'id' => $supplierId,
            'message' => Lang::get('lang.supplier_details') . ' ' . Lang::get('lang.successfully_added')
        ];

        return response()->json($response, 200);
    }

    public function getSupplierData(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $requestType = $request->reqType;

        $suppliers = Supplier::getSuppliers($columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);

        return ['datarows' => $suppliers['data'], 'count' => $suppliers['count']];
    }

    public function getData($id)
    {
        $fields = ['id', 'first_name', 'last_name', 'email', 'company', 'phone_number', 'tin_number', 'address'];
        $supplier = Supplier::getFirst($fields, 'id', $id);
        $supplier->fullName = $supplier->first_name . " " . $supplier->last_name;

        return ['supplierData' => $supplier];
    }

    public function editSupplierData(Request $request, $id)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
        ]);

        $data = array();
        $data['first_name'] = $request->input('first_name');
        $data['last_name'] = $request->input('last_name');
        $data['email'] = $request->input('email');
        $data['company'] = $request->input('company');
        $data['phone_number'] = $request->input('phone_number');
        $data['tin_number'] = $request->input('tin_number');
        $data['address'] = $request->input('address');
        $data['created_by'] = Auth::user()->id;

        Supplier::updateData($id, $data);

        $response = [
            'message' => Lang::get('lang.supplier') . ' ' . Lang::get('lang.successfully_updated')
        ];

        return response()->json($response, 200);
    }

    public function getSupplierDetails($id)
    {
        $tabName = '';
        $routeName = '';
        $perm = new PermissionController;
        $permission = $perm->supplierDetailsPermission();

        if ($permission) {

            $fields = ['id', 'first_name', 'last_name', 'email', 'company', 'phone_number', 'address', 'avatar', 'tin_number'];
            $supplierDetails = Supplier::getFirst($fields, 'id', $id);

            $supplierDetails->fullName = $supplierDetails->first_name . " " . $supplierDetails->last_name;

            if (isset($_GET['tab_name'])) {
                $tabName = $_GET['tab_name'];
            }
            if (isset($_GET['route_name'])) {
                $routeName = $_GET['route_name'];
            }


            return view(
                'suppliers.SupplierDetails',
                [
                    'supplierDetails' => $supplierDetails,
                    'tab_name' => $tabName,
                    'route_name' => $routeName
                ]
            );
        } else {

            abort(404);
        }
    }

    public function getSupplierDeliveryRecords(Request $request, $id)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $offset = $request->rowOffset;
        $searchValue = '';
        $requestType = $request->reqType;
        if ($request->searchValue) $searchValue = $request->searchValue;
        if ($columnName == 'id') $columnName = 'id';
        $columnSortedBy = $request->columnSortedBy;
        if (empty($filtersData)) {
            $supplierRecords = Order::supplierRecords($columnName, $columnSortedBy, $limit, $offset, $id, $searchValue, $requestType);
        } else {
            $supplierRecords = Order::supplierRecordsByDate($columnName, $limit, $offset, $searchValue, $id, $columnSortedBy, $filtersData, $requestType);
        }
        $records = $supplierRecords['data'];
        $total = $this->calculateSupplierRecords($records);
        if (empty($requestType)) {
            $count = $total['count'];
            $records[$count] = ['id' => Lang::get('lang.total'), 'item_received' => $total['item_received'], 'sub_total' => $total['sub_total'], 'tax' => $total['tax'], 'discount' => $total['discount'], 'total' => $total['total'], 'due_amount' => $total['due_amount']];

            $grandTotal = $this->calculateSupplierRecords($supplierRecords['allData']);
            $records[$count + 1] = ['id' => Lang::get('lang.grand_total'), 'item_received' => $grandTotal['item_received'], 'sub_total' => $grandTotal['sub_total'], 'tax' => $grandTotal['tax'], 'discount' => $grandTotal['discount'], 'total' => $grandTotal['total'], 'due_amount' => $grandTotal['due_amount']];
        }
        return ['datarows' => $records, 'count' => $supplierRecords['count']];
    }

    public function calculateSupplierRecords($supplierRecord)
    {
        $netItem = 0;
        $netSubTotal = 0;
        $netDue = 0;
        $netTotal = 0;
        $netDiscount = 0;
        $netTax = 0;
        $count = 0;
        foreach ($supplierRecord as $rowData) {
            $netItem += $rowData->item_received;
            $netSubTotal += $rowData->sub_total;
            $netTotal += $rowData->total;
            $netDiscount += $rowData->discount;
            $netTax += $rowData->tax;
            $netDue += $rowData->due_amount;

            $count++;
        }

        return ['item_received' => $netItem, 'sub_total' => $netSubTotal, 'tax' => $netTax, 'discount' => $netDiscount, 'total' => $netTotal, 'due_amount' => $netDue, 'count' => $count];
    }

    public function deleteSupplier($id)
    {
        $used = Order::countRecord('supplier_id', $id);
        if ($used == 0) {
            Supplier::deleteData($id);
            $response = [
                'message' => Lang::get('lang.supplier') . ' ' . Lang::get('lang.deleted_successfully'),
            ];

            return response()->json($response, 200);
        } else {
            $response = [
                'message' => Lang::get('lang.can_not_delete'),
            ];
            return response()->json($response, 200);
        }
    }

    public function updateAvatar(Request $request, $id)
    {
        $data = array();
        $imageHandler = new imageHandler;
        $data['avatar'] = $imageHandler->imageUpload($request->avatar, 'supplier_', 'uploads/profile/');
        $previousAvatar = Supplier::getFirst('avatar', 'id', $id);

        if ($previousAvatar->avatar != 'default.jpg') {
            unlink('uploads/profile/' . $previousAvatar->avatar);
        }
        Supplier::updateData($id, $data);
        $message = Lang::get('lang.supplier_avatar') . ' ' . Lang::get('lang.successfully_updated');
        return response()->json(['message' => $message]);
    }

    public function importSuppliers(Request $request)
    {
        $FrontendEmailInfo = [];

        foreach ($request->importData as $frontendEmail) {
            $emailRecord = (object) $frontendEmail;
            array_push($FrontendEmailInfo, $emailRecord->EMAIL);
        }
        $allMails = Supplier::getAllEmails();

        $matchEmail = [];
        foreach ($allMails as $email) {
            if (in_array($email['email'], $FrontendEmailInfo)) {
                array_push($matchEmail, $email['email']);
            }
        }

        $allSupplierRecords = [];
        foreach ($request->importData as $supplier) {

            $supplierRecord = (object) $supplier;

            if ($supplierRecord->FIRST_NAME != null && $supplierRecord->LAST_NAME != null && $supplierRecord->EMAIL != null && count($matchEmail) == 0) {

                $data = array();
                $data['FIRST_NAME'] = $supplierRecord->FIRST_NAME;
                $data['LAST_NAME'] = $supplierRecord->LAST_NAME;
                $data['EMAIL'] = $supplierRecord->EMAIL;
                $data['COMPANY'] = $supplierRecord->COMPANY;
                $data['PHONE_NUMBER'] = $supplierRecord->PHONE_NUMBER;
                $data['ADDRESS'] = $supplierRecord->ADDRESS;
                array_push($allSupplierRecords, $data);
            } else {
                $showInvalidData = $request->importData;
                $invalidDataCollection = [];
                $allErrorPreview = [];
                foreach ($showInvalidData as $supplierErrorRecord) {
                    $flag = 0;
                    $invalidRecord = (object) $supplierErrorRecord;
                    if ($invalidRecord->FIRST_NAME == null || $invalidRecord->LAST_NAME == null || $supplierRecord->EMAIL == null) {
                        $flag = 1;
                        $message = Lang::get('lang.first_name_last_name_and_email_is_requir');
                    }
                    if (in_array($invalidRecord->EMAIL, $matchEmail)) {
                        if ($flag == 1) {
                            $message = Lang::get('lang.first_name_and_last_name_require_email_already_exit');
                        } else {
                            $message = Lang::get('lang.this_email_are_already_exit');
                        }
                    }
                    $duplicateEmal = array_count_values($FrontendEmailInfo);
                    if ($duplicateEmal[$invalidRecord->EMAIL] > 1) {
                        if ($flag == 1) {
                            $message = Lang::get('lang.first_name_and_last_name_require_email_is_duplicate');
                        } else {
                            $message = Lang::get('lang.this_email_is_duplicate');
                        }
                    }
                    if ($invalidRecord->FIRST_NAME == null || $invalidRecord->LAST_NAME == null || $supplierRecord->EMAIL == null || in_array($invalidRecord->EMAIL, $matchEmail) || $duplicateEmal[$invalidRecord->EMAIL] > 1) {
                        $invalidDataCollection[] = array(
                            'FIRST_NAME' => $invalidRecord->FIRST_NAME,
                            'LAST_NAME' => $invalidRecord->LAST_NAME,
                            'EMAIL' => $invalidRecord->EMAIL,
                            'COMPANY' => $invalidRecord->COMPANY,
                            'PHONE_NUMBER' => $invalidRecord->PHONE_NUMBER,
                            'ADDRESS' => $invalidRecord->ADDRESS,
                            'INVALID_DATA' => $message,
                        );
                    } else {
                        $invalidDataCollection[] = array(
                            'FIRST_NAME' => $invalidRecord->FIRST_NAME,
                            'LAST_NAME' => $invalidRecord->LAST_NAME,
                            'EMAIL' => $invalidRecord->EMAIL,
                            'COMPANY' => $invalidRecord->COMPANY,
                            'PHONE_NUMBER' => $invalidRecord->PHONE_NUMBER,
                            'ADDRESS' => $invalidRecord->ADDRESS,
                        );
                    }
                }
                foreach ($showInvalidData as $data) {
                    if ($data['FIRST_NAME'] == null) {
                        array_push($allErrorPreview, $data['FIRST_NAME']);
                    }
                    if ($data['LAST_NAME'] == null) {
                        array_push($allErrorPreview, $data['LAST_NAME']);
                    }
                    if ($data['EMAIL'] == null) {
                        array_push($allErrorPreview, $data['EMAIL']);
                    }
                }
                $columnTitles = $request->requiredColumns;
                array_push($columnTitles, 'INVALID_DATA');
                $response = [
                    'message' => Lang::get('lang.invalid_data_download_file_to_see_the_error'),
                    'excelInvalidDatas' => $invalidDataCollection,
                    'requiredColumns' => $columnTitles,
                    'errorPreviewData' => $allErrorPreview
                ];
                return response()->json($response, 400);
            }
        }
        $supplierInfo = Supplier::insertData($allSupplierRecords);
        if ($supplierInfo) {
            $response = [
                'message' => Lang::get('lang.supplier') . ' ' . Lang::get('lang.successfully_imported_from_your_file')
            ];
            return response()->json($response, 201);
        }
    }
}
