<?php

namespace Stu\Orm\Entity;

use Stu\Module\PlayerSetting\Lib\UserSettingEnum;

interface UserSettingInterface
{
    public function setUser(UserInterface $user): UserSettingInterface;

    public function setSetting(UserSettingEnum $setting): UserSettingInterface;

    public function getValue(): string;

    public function setValue(string $value): UserSettingInterface;
}
