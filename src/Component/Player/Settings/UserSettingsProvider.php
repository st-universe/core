<?php

declare(strict_types=1);

namespace Stu\Component\Player\Settings;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Player\UserCssClassEnum;
use Stu\Component\Player\UserRpgBehaviorEnum;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserSettingInterface;

class UserSettingsProvider implements UserSettingsProviderInterface
{
    #[Override]
    public function getRgbCode(UserInterface $user): string
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::RGB_CODE->value);
        if ($setting !== null) {
            return $setting->getValue();
        }

        return '';
    }

    #[Override]
    public function getCss(UserInterface $user): UserCssClassEnum
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::CSS_COLOR_SHEET->value);
        if ($setting !== null) {
            return UserCssClassEnum::from($setting->getValue());
        }

        return UserCssClassEnum::BLACK;
    }

    #[Override]
    public function getAvatar(UserInterface $user): string
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::AVATAR->value);
        if ($setting !== null) {
            return $setting->getValue();
        }

        return '';
    }

    #[Override]
    public function isEmailNotification(UserInterface $user): bool
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::EMAIL_NOTIFICATION->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function isStorageNotification(UserInterface $user): bool
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::STORAGE_NOTIFICATION->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function isShowOnlineState(UserInterface $user): bool
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::SHOW_ONLINE_STATUS->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function isShowPmReadReceipt(UserInterface $user): bool
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::SHOW_PM_READ_RECEIPT->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function isSaveLogin(UserInterface $user): bool
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::SAVE_LOGIN->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function getFleetFixedDefault(UserInterface $user): bool
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::FLEET_FIXED_DEFAULT->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function getWarpsplitAutoCarryoverDefault(UserInterface $user): bool
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::WARPSPLIT_AUTO_CARRYOVER_DEFAULT->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function getDefaultView(UserInterface $user): ModuleEnum
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::DEFAULT_VIEW->value);
        if ($setting !== null) {
            return ModuleEnum::from($setting->getValue());
        }

        return ModuleEnum::MAINDESK;
    }

    #[Override]
    public function getRpgBehavior(UserInterface $user): UserRpgBehaviorEnum
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::RPG_BEHAVIOR->value);
        if ($setting !== null) {
            return UserRpgBehaviorEnum::from((int)$setting->getValue());
        }

        return UserRpgBehaviorEnum::NOT_SET;
    }

    #[Override]
    public function isShowPirateHistoryEntrys(UserInterface $user): bool
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::SHOW_PIRATE_HISTORY_ENTRYS->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function isInboxMessengerStyle(UserInterface $user): bool
    {
        $setting = $this->getSettings($user)->get(UserSettingEnum::INBOX_MESSENGER_STYLE->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    /**
     * @return Collection<string, UserSettingInterface>
     */
    private function getSettings(UserInterface $user): Collection
    {
        return $user->getSettings();
    }
}
