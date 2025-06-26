<?php

namespace Stu\Module\PlayerSetting\Lib;

use Stu\Orm\Entity\User;

interface ChangeUserSettingInterface
{
    public function change(User $user, UserSettingEnum $setting, string $value): void;

    public function reset(User $user, UserSettingEnum $setting): void;
}
