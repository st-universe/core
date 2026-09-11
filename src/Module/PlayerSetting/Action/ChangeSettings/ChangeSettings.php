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
    public const string ACTION_IDENTIFIER = 'B_CHANGE_SETTINGS';

    public function __construct(private ChangeUserSettingInterface $changeUserSetting) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        foreach (UserSettingEnum::getNonDistinct() as $setting) {
            if (!request::has($setting->value)) {
                $this->changeUserSetting->reset($user, $setting);
            } else {

                $value = request::postStringFatal($setting->value);
                $this->changeUserSetting->change(
                    $user,
                    $setting,
                    $value
                );
            }
        }

        $game->getInfo()->addInformation(_('Die Accounteinstellungen wurden aktualisiert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
