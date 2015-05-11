<?php
namespace app\utils;

/**
 * 配列のヘルパー
 */
class Arr
{
    /**
     * 平坦化した配列を返す
     *
     * @param array $arr
     * @return array
     */
    public static function flatten(array $arr)
    {
        $tmp = [];
        array_walk_recursive($arr, function($v, $k) use (&$tmp) {
            $tmp[] = $v;
        });
        return $tmp;
    }
}
