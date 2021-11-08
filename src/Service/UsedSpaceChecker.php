<?php

namespace App\Service;

use App\Entity\User;
use App\Helper\SizesHelper;

class UsedSpaceChecker
{

    public function getUsedSpace(User $user): array
    {
        $files = $user->getFiles();
        $allFilesSize = 0;
        foreach ($files as $file){
            $allFilesSize += (int)$file->getFilesize();
        }
        $limit = $user->getDiskLimit();
        if($limit === "no_limit"){
            $spaceLeft = 0;
            $percent = 0;
        }else{
            if($allFilesSize < $limit){
                $spaceLeft = $limit - $allFilesSize;
                $percent = number_format(($allFilesSize / $limit) * 100, 0);
            }else{
                $percent = (string)100;
                $spaceLeft = "Przekroczono limit, możliwość przesyłania plików wyłączona.";
            }
        }
        return array(
            "limit"   => SizesHelper::getOptimalSize($limit),
            "files"   => SizesHelper::getOptimalSize($allFilesSize),
            "left"   => SizesHelper::getOptimalSize($spaceLeft),
            "percent" => $percent,
        );
    }
}