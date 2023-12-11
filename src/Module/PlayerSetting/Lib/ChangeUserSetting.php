<?php

namespace Stu\Module\PlayerSetting\Lib;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserSettingRepositoryInterface;

final class ChangeUserSetting implements ChangeUserSettingInterface
{
    private UserSettingRepositoryInterface $userSettingRepository;

    public function __construct(UserSettingRepositoryInterface $userSettingRepository)
    {
        $this->userSettingRepository = $userSettingRepository;
    }

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

    public function reset(UserInterface $user, UserSettingEnum $setting): void
    {
        $userSetting = $user->getSettings()->get($setting->value);
        if ($userSetting !== null) {
            $user->getSettings()->removeElement($userSetting);
            $this->userSettingRepository->delete($userSetting);
        }
    }
}
