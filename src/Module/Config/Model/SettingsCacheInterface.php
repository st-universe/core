<?php

namespace Stu\Module\Config\Model;

use Stu\Module\Config\StuConfigSettingEnum;

interface SettingsCacheInterface
{
    public function getSettings(StuConfigSettingEnum $type, ?SettingsInterface $parent): SettingsInterface;
}
