<?php

namespace App\Helper;

class SizesHelper
{
    private const SIZES = array("B", "KB", "MB", "GB", "TB");
        //TODO: how to make it work ?
    public static function getOptimalSize($size): array
    {
        $num = $size;
        $order = 0;
        while ($num >= 1024 && $order < sizeof(self::SIZES) - 1) {
            $order++;
            $num = $num/1024;
        }
        $unit = self::SIZES[$order];
        return array(
            "number" => $num,
            "units" => $unit
        );
    }

}