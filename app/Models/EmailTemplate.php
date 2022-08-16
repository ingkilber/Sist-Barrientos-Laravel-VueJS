<?php

namespace App\Models;

class EmailTemplate extends BaseModel
{
    protected $fillable = ['template_type', 'template_subject', 'default_content', 'custom_content'];

    public static function getEmailTemplate($request)
    {
        return EmailTemplate::orderBy($request->columnKey, $request->columnSortedBy)->get();
    }

    public function getEmailTemplateCount()
    {
        return parent::countData();
    }

    public function findEmailTemplate($id)
    {
        return parent::findRow($id);
    }

    public function updateEmailTemplate($id, $subject, $custom_content)
    {
        return parent::updateRow($id, ['template_subject' => $subject, 'custom_content' => $custom_content]);
    }

    public static function getContent()
    {
        return EmailTemplate::select('template_subject', 'default_content', 'custom_content')->where('template_type', 'reset_password')->first();
    }

    public static function getLowStockTemplate()
    {
        $emailTemplate = EmailTemplate::select('*')->where('template_type', 'low_stock')->first();

        if ($emailTemplate->custom_content != '') {
            return $emailTemplate->custom_content;
        } else {
            return $emailTemplate->default_content;
        }
    }
}
