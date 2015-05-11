<?php
namespace app\utils;

class String
{
    /**
     * 全角空白にも対応したtrim
     *
     * @param string $value
     * @return string
     */
    public static function mb_trim($value)
    {
        static $chars = "[\\x0-\x20\x7f\xc2\xa0\xe3\x80\x80]";
        return preg_replace("/\A{$chars}++|{$chars}++\z/u", '', $value);
    }
}
