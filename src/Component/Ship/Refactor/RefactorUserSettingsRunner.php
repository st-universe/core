<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Refactor;

use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\UserSettingRepositoryInterface;

final class RefactorUserSettingsRunner
{
    private UserRepositoryInterface $userRepository;

    private UserSettingRepositoryInterface $userSettingRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserSettingRepositoryInterface $userSettingRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userSettingRepository = $userSettingRepository;
    }

    public function refactor(): void
    {
        foreach ($this->userRepository->findAll() as $user) {

            $this->createUserSetting($user, UserSettingEnum::AVATAR, $user->getAvatar());
            $this->createUserSetting($user, UserSettingEnum::RGB_CODE, $user->getRgbCode());
            $this->createUserSetting($user, UserSettingEnum::DEFAULT_VIEW, $user->getDefaultView()->value);
            $this->createUserSetting($user, UserSettingEnum::EMAIL_NOTIFICATION, $user->isEmailNotification());
            $this->createUserSetting($user, UserSettingEnum::STORAGE_NOTIFICATION, $user->isStorageNotification());
            $this->createUserSetting($user, UserSettingEnum::SHOW_ONLINE_STATUS, $user->isShowOnlineState());
            $this->createUserSetting($user, UserSettingEnum::SHOW_PM_READ_RECEIPT, $user->isShowPmReadReceipt());
            $this->createUserSetting($user, UserSettingEnum::FLEET_FIXED_DEFAULT, $user->getFleetFixedDefault());
            $this->createUserSetting($user, UserSettingEnum::RPG_BEHAVIOR, $user->getRpgBehavior()->value);
            $this->createUserSetting($user, UserSettingEnum::CSS_COLOR_SHEET, $user->getCss());
            $this->createUserSetting($user, UserSettingEnum::SHOW_PIRATE_HISTORY_ENTRYS, $user->isShowPirateHistoryEntrys());
        }
    }

    private function createUserSetting(
        UserInterface $user,
        UserSettingEnum $setting,
        mixed $value
    ): void {

        if (empty($value)) {
            return;
        }

        $userSetting = $this->userSettingRepository->prototype();

        $userSetting
            ->setUser($user)
            ->setSetting($setting)
            ->setValue((string) $value);

        $this->userSettingRepository->save($userSetting);
    }
}