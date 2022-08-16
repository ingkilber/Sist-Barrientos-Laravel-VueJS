<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SmsTemplate extends BaseModel
{
    protected $fillable = ['template_type', 'template_subject', 'default_content', 'custom_content'];

    public static function getSmsTemplate($request)
    {
        return SmsTemplate::orderBy($request->columnKey, $request->columnSortedBy)->get();
    }

    public function getSmsemplateCount()
    {
        return parent::countData();
    }
}
