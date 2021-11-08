<?php

namespace App\Helper;

class SizesHelper
{
    private const SIZES = array("B", "KB", "MB", "GB", "TB");
    private const BREAKPOINT = 800;
    public static function getOptimalSize($size): array
    {
        $num = $size;
        $order = 0;
        while ($num >= self::BREAKPOINT && $order < sizeof(self::SIZES)) {
            $order++;
            $num = $num/1024;
        }
        $unit = self::SIZES[$order];
        return array(
            "number" => round($num,2),
            "unit" => $unit
        );
    }

}