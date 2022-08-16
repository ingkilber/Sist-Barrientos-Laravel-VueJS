<?php

namespace App\Http\Controllers\API;

use App\Libraries\Permissions;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Order;
use App\Models\SmsTemplate;
use App\Models\Setting;
use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\PermissionController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use DB;
use App\Libraries\imageHandler;
use App\Libraries\searchHelper;
use App\Libraries\SmsHelper;
use Illuminate\Support\Facades\Config;


class CustomerController extends Controller
{

    public function permissionCheck()
    {
        return new Permissions;
    }

    public function getCustomerList(Request $request)
    {
        $searchValue = '';
        $typeFilter = '';

        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;
        $requestType = $request->reqType;

        if ($request->filtersData) {
            $filtersData = $request->filtersData;

            foreach ($filtersData as $filter) {
                if ($filter['key'] === 'customerGroups') $typeFilter = $filter['value'];
            }
        }

        if (searchHelper::inputSearch($request->searchValue)) $searchValue = searchHelper::inputSearch($request->searchValue);

        if ($columnName == 'full_name') $columnName = 'first_name';
        else if ($columnName == 'customer_group_title') $columnName = 'customer_group';

        $customers = Customer::getCustomers($searchValue, $typeFilter, $columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);

        foreach ($customers['data'] as $user) {
            $user->full_name = $user->first_name . " " . $user->last_name;
        }

        return ['datarows' => $customers['data'], 'count' => $customers['count']];
    }

    public function store(Request $request)
    {
        $autoSmsReceive = Setting::getSettingValue('new_customer_welcome_sms')->setting_value;
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'customer_group' => 'required',
        ]);
        $customerData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'company' => $request->company,
            'tin_number' => $request->tin_number,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'customer_group' => $request->customer_group,
            'created_by' => Auth::user()->id
        ];
        if ($autoSmsReceive == 1 && $request->phone_number) {
            $this->customerWelcomeSms($request->first_name, $request->last_name, $request->phone_number);
        }

        if ($customer = Customer::store($customerData)) {
            $response = [
                'data' => $customer,
                'message' => Lang::get('lang.customer') . ' ' . Lang::get('lang.successfully_saved')
            ];
            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    // auto sms receive customer
    protected function autoSmsSend($phone_number, $sendSmsText)
    {
        return SmsHelper::sendSms($phone_number, $sendSmsText);
    }

    private function customerWelcomeSms($first_name, $last_name, $phone_number)
    {
        try {
            if ($phone_number) {

                $smsText = SmsTemplate::select('template_subject', 'default_content', 'custom_content')->where('template_type', 'customer_welcome_sms')->first();
                if ($smsText->custom_content) {
                    $text = $smsText->custom_content;
                } else {
                    $text = $smsText->default_content;
                }
                $sendSmsText = str_replace('{first_name}', $first_name, str_replace('{last_name}', $last_name, str_replace('{app_name}', Config::get('app_name'), $text)));

                $this->autoSmsSend($phone_number, $sendSmsText);

                $response = [
                    'message' => Lang::get('lang.successfully_sms')
                ];

                return response()->json($response, 200);

            }
        } catch (\Exception $e) {
            $response = [
                'message' => Lang::get('lang.phone_number_wrong')
            ];

            return response()->json($response, 201);
        }
    }

    public function updateCustomer(Request $request, $id)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'customer_group' => 'required',
        ]);
        if ($customer = Customer::getOne($id)) {
            $customer->updateData($id, $request->all());
            $response = [
                'message' => Lang::get('lang.customer_details') . ' ' . Lang::get('lang.successfully_updated')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    public function destroy($id)
    {
        if ($this->permissionCheck()->isAdmin()) {
            Customer::deleteData($id);
            $response = [
                'message' => Lang::get('lang.deleted_successfully'),
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'msg' => Lang::get('lang.permission_error'),
                'Customer' => Lang::get('lang.permission_is_not_available')
            ];

            return response()->json($response, 401);
        }
    }

    public function getCustomerData($id)
    {
        $customer = Customer::getOne($id);
        return ['customer' => $customer];
    }

    public function deleteCustomer($id)
    {
        $used = Order::countRecord('customer_id', $id);
        if ($used == 0) {
            Customer::deleteData($id);
            $response = [
                'message' => Lang::get('lang.customer') . ' ' . Lang::get('lang.deleted_successfully'),
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
        $data['avatar'] = $imageHandler->imageUpload($request->avatar, 'customer_', 'uploads/profile/');
        $previousAvatar = Customer::getFirst('avatar', 'id', $id);

        if ($previousAvatar->avatar != 'default.jpg') {
            unlink('uploads/profile/' . $previousAvatar->avatar);
        }
        Customer::updateData($id, $data);
        $message = Lang::get('lang.customer_avatar') . ' ' . Lang::get('lang.successfully_updated');
        return response()->json(['message' => $message]);
    }

//customer import for excel file
    public function importCustomers(Request $request)
    {
        $created_by = Auth::user()->id;
        $FrontendEmailInfo = [];
        foreach ($request->importData as $frontendEmail) {
            $emailRecord = (object)$frontendEmail;
            $emailData = array();

            $emailData['email'] = $emailRecord->EMAIL;
            array_push($FrontendEmailInfo, $emailData);
        }

        $AllFrontendEmails = Arr::pluck($FrontendEmailInfo, 'email');
        $customers = Customer::all();
        $emails = Arr::pluck($customers, 'email');

        $matchEmail = [];
        foreach ($AllFrontendEmails as $value) {
            if (in_array($value, $emails)) {
                array_push($matchEmail, $value);
            }
        }
        $allCustomerRecords = [];
        $invalidDataCollection = [];

        foreach ($request->importData as $index => $customer) {
            $customerRecord = (object)$customer;
            if ($customerRecord->FIRST_NAME != null && $customerRecord->LAST_NAME != null && count($matchEmail) == 0) {
                $data = array();
                $data['first_name'] = $customerRecord->FIRST_NAME;
                $data['last_name'] = $customerRecord->LAST_NAME;
                $data['email'] = $customerRecord->EMAIL;
                $data['company'] = $customerRecord->COMPANY;
                $data['phone_number'] = $customerRecord->PHONE_NUMBER;
                $data['address'] = $customerRecord->ADDRESS;
                if ($customerRecord->CUSTOMER_GROUP == null) {
                    $customerGroupId = 1;
                } else {
                    $count = CustomerGroup::isCustomerGroupExists($customerRecord->CUSTOMER_GROUP);
                    if ($count == 0) {
                        $customerGroupData['title'] = $customerRecord->CUSTOMER_GROUP;
                        $customerGroupData['discount'] = 0;
                        $customerGroupData['is_default'] = 0;
                        $customerGroupData['created_by'] = $created_by;
                        $customerGroupId = CustomerGroup::getInsertedId($customerGroupData);

                    } else {
                        $isCustomerGroupExists = Customer::getCustomerGroup($customerRecord->CUSTOMER_GROUP);
                        $customerGroupId = $isCustomerGroupExists['id'];
                    }
                }

                $data['customer_group'] = $customerGroupId;
                array_push($allCustomerRecords, $data);

            } else {
                $showInvalidData = $request->importData;
                $allErrorPreview = [];
                foreach ($showInvalidData as $customerErrorRecord) {
                    $flag = 0;
                    $invalidRecord = (object)$customerErrorRecord;

                    if ($invalidRecord->FIRST_NAME == null || $invalidRecord->LAST_NAME == null) {
                        $flag = 1;
                        $message = Lang::get('lang.first_name & last_name is requir');
                    }

                    if (in_array($invalidRecord->EMAIL, $matchEmail)) {
                        if ($flag == 1) {
                            $message = Lang::get('lang.first_name_and_last_name_require_email_already_exit');
                        } else {
                            $message = Lang::get('lang.this_email_are_already_exit');
                        }
                    }

                    $duplicateEmail = array_count_values($AllFrontendEmails);

                    if ($duplicateEmail[$invalidRecord->EMAIL] > 1) {
                        if ($flag == 1) {
                            $message = Lang::get('lang.first_name_and_last_name_require_email_is_duplicate');
                        } else {
                            $message = Lang::get('lang.this_email_is_duplicate');
                        }
                    }

                    if ($invalidRecord->FIRST_NAME == null || $invalidRecord->LAST_NAME == null || in_array($invalidRecord->EMAIL, $matchEmail) || $duplicateEmail[$invalidRecord->EMAIL] > 1) {
                        $invalidDataCollection[] = array(
                            'FIRST_NAME' => $invalidRecord->FIRST_NAME,
                            'LAST_NAME' => $invalidRecord->LAST_NAME,
                            'EMAIL' => $invalidRecord->EMAIL,
                            'COMPANY' => $invalidRecord->COMPANY,
                            'PHONE_NUMBER' => $invalidRecord->PHONE_NUMBER,
                            'ADDRESS' => $invalidRecord->ADDRESS,
                            'CUSTOMER_GROUP' => $invalidRecord->CUSTOMER_GROUP,
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
                            'CUSTOMER_GROUP' => $invalidRecord->CUSTOMER_GROUP,
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

        $customersInfo = Customer::insertData($allCustomerRecords);

        if ($customersInfo) {
            $response = [
                'message' => Lang::get('lang.customer') . ' ' . Lang::get('lang.successfully_imported_from_your_file')
            ];

            return response()->json($response, 201);
        }
    }
}

