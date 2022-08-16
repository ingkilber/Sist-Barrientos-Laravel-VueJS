<?php

namespace App\Libraries;


class searchHelper
{
    public static function inputSearch($text)
    {
           $text = rtrim($text);
            return $text;
    }
}