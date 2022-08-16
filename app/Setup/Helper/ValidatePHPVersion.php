<?php


namespace App\Setup\Helper;


use App\Exceptions\GeneralException;

trait ValidatePHPVersion
{
    public function validatePHPVersion()
    {
        $number = config('install.core.min_php_version');

        throw_if(
            phpversion() <= $number,
            new GeneralException(trans('default.please_update_your_php_version_to_number', ['number' => $number]), 404)
        );
    }
}
