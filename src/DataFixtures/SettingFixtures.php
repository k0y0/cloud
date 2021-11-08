<?php

namespace App\DataFixtures;

use App\Entity\Setting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SettingFixtures extends Fixture
{
    private const SETTINGS = array(
//        array("name" => "setting_name", "value" => "setting_value"),
        array("name" => "system_maintenance" , "value" => "false"), // false means not in maintenance mode
        array("name" => "default_user_disk_limit" , "value" => 1073741824 ), //every user should have 1gb of "free" space
    );

    public function load(ObjectManager $manager): void
    {

        foreach (self::SETTINGS as $setting){
            $a = new Setting();
            $a->setSettingName($setting['name']);
            $a->setSettingValue($setting['value']);
            $manager->persist($a);
            $manager->flush();
        }
    }
}
