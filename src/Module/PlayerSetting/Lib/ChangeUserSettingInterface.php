<?php

namespace Stu\Module\PlayerSetting\Lib;

use Stu\Orm\Entity\UserInterface;

interface ChangeUserSettingInterface
{
    public function change(UserInterface $user, UserSettingEnum $setting, string $value): void;

    public function reset(UserInterface $user, UserSettingEnum $setting): void;
}
