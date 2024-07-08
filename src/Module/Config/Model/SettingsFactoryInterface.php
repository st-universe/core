<?php

namespace Stu\Module\Config\Model;

use Stu\Module\Config\StuConfigSettingEnum;

interface SettingsFactoryInterface
{
    public function createSettings(
        StuConfigSettingEnum $type,
        ?SettingsInterface $parent,
        SettingsCacheInterface $settingsCache
    ): SettingsInterface;
}
