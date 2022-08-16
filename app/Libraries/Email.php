<?php

namespace App\Libraries;


use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Config;

class Email
{
    public function sendEmail($mailText, $email, $subject, $fileNameToStore = null)
    {

        $settingCollection = Setting::all();

        $fromName = $settingCollection->where('setting_name', 'email_from_name')->pluck('setting_value')->first();
        $fromAddress = $settingCollection->where('setting_name', 'email_from_address')->pluck('setting_value')->first();
        $fromPass = $settingCollection->where('setting_name', 'email_smtp_password')->pluck('setting_value')->first();
        $fromDriver = $settingCollection->where('setting_name', 'email_driver')->pluck('setting_value')->first();
        $fromHost = $settingCollection->where('setting_name', 'email_smtp_host')->pluck('setting_value')->first();
        $fromPort = $settingCollection->where('setting_name', 'email_port')->pluck('setting_value')->first();
        $fromType = $settingCollection->where('setting_name', 'email_encryption_type')->pluck('setting_value')->first();
        $mailgunDomain = $settingCollection->where('setting_name', 'mailgun_domain')->pluck('setting_value')->first();
        $mailgunApi = $settingCollection->where('setting_name', 'mailgun_api')->pluck('setting_value')->first();
        $mandrill = $settingCollection->where('setting_name', 'mandrill_api')->pluck('setting_value')->first();
        $sparkpost = $settingCollection->where('setting_name', 'sparkpost_api')->pluck('setting_value')->first();

        if ($fromDriver && $fromAddress) {
            Config::set('mail.username', $fromAddress);
            Config::set('mail.password', $fromPass);
            Config::set('mail.host', $fromHost);
            Config::set('mail.driver', $fromDriver);
            Config::set('mail.port', $fromPort);
            Config::set('mail.encryption', $fromType);
            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);
            Config::set('services.mandrill.secret', $mandrill);
            Config::set('services.sparkpost.secret', $sparkpost);
            Config::set('services.mailgun.domain', $mailgunDomain);
            Config::set('services.mailgun.secret', $mailgunApi);


            if ($fileNameToStore != null) {

                $pdf = public_path('/storage/pdf/' . $fileNameToStore);

                Mail::send([], [], function ($message) use ($email, $subject, $mailText, $pdf) {
                    $message->to($email)->subject($subject)->setBody($mailText, 'text/html')->Attach(\Swift_Attachment::fromPath($pdf));
                });
            } else {
                Mail::send([], [], function ($message) use ($email, $subject, $mailText) {
                    $message->to($email)->subject($subject)->setBody($mailText, 'text/html');
                });
            }

            return true;
        } else {
            return false;
        }
    }
}
