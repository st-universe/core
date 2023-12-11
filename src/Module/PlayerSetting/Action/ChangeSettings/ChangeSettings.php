<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeSettings;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\ChangeUserSettingInterface;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;

final class ChangeSettings implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_SETTINGS';

    private ChangeUserSettingInterface $changeUserSetting;

    public function __construct(
        ChangeUserSettingInterface $changeUserSetting
    ) {
        $this->changeUserSetting = $changeUserSetting;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        foreach (UserSettingEnum::cases() as $setting) {
            if (!request::has($setting->value)) {
                $this->changeUserSetting->reset($user, $setting);
            } else {

                $this->changeUserSetting->change(
                    $user,
                    $setting,
                    request::postStringFatal($setting->value)
                );
            }
        }

        $game->addInformation(_('Die Accounteinstellungen wurden aktualisiert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
