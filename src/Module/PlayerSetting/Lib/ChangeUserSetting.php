<?php

namespace Stu\Module\PlayerSetting\Lib;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserSettingRepositoryInterface;

final class ChangeUserSetting implements ChangeUserSettingInterface
{
    public function __construct(private UserSettingRepositoryInterface $userSettingRepository)
    {
    }

    #[Override]
    public function change(UserInterface $user, UserSettingEnum $setting, string $value): void
    {
        $userSetting = $user->getSettings()->get($setting->value);
        if ($userSetting === null) {
            $userSetting = $this->userSettingRepository->prototype();
            $userSetting
                ->setUser($user)
                ->setSetting($setting);

            $user->getSettings()->set($setting->value, $userSetting);
        }

        $userSetting->setValue($value);

        $this->userSettingRepository->save($userSetting);
    }

    #[Override]
    public function reset(UserInterface $user, UserSettingEnum $setting): void
    {
        $userSetting = $user->getSettings()->get($setting->value);
        if ($userSetting !== null) {
            $user->getSettings()->removeElement($userSetting);
            $this->userSettingRepository->delete($userSetting);
        }
    }
}
