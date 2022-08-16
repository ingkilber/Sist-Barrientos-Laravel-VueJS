<?php


namespace App\Libraries;


use Illuminate\Support\Facades\Config;

class SmsHelper
{

    public static function sendSms($phone, $sendSmsText)
    {
        $basic  = new \Nexmo\Client\Credentials\Basic(config("key"), config("secret_key"));
        $client = new \Nexmo\Client($basic);
        $message = $client->message()->send([
            'to' => preg_replace('/(\W*)/', '', $phone),
            'from' => config("sms_from_name_phone_number"),
            'text' => $sendSmsText
        ]);

        return $message;
    }
}