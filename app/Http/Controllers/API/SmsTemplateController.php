<?php

namespace App\Http\Controllers\API;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;


class SmsTemplateController extends Controller
{
    public function index(Request $request)
    {
        $data = SmsTemplate::getSmsTemplate($request);
        $totalCount = SmsTemplate::countData();
        return ['datarows' => $data, 'count' => $totalCount];
    }

    public function show($id)
    {
        $smsTemplate = SmsTemplate::getOne($id);

        if ($smsTemplate->custom_content) {
            $response = [
                'smsSubject' => $smsTemplate->template_subject,
                'content' => $smsTemplate->custom_content,
                'isCustom' => true,
            ];
        } else {
            $response = [
                'smsSubject' => $smsTemplate->template_subject,
                'content' => $smsTemplate->default_content,
                'isCustom' => false,
            ];
        }

        return $response;
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'subject' => 'required',
        ]);

        $success = SmsTemplate::updateData($id, [
            'template_subject' => $request->input('subject'),
            'custom_content' => $request->input('custom_content')
        ]);

        if (!$success) {
            return response()->json([
                'message' => Lang::get('lang.error_during_update')
            ], 404);
        }

        $response = [

            'message' => ucfirst(strtolower(Lang::get('lang.' . $request->template_name) . ' ' . Lang::get('lang.settings_saved_successfully')))
        ];

        return response()->json($response, 200);
    }
}
