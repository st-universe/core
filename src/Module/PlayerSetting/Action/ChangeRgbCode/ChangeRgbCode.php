<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeRgbCode;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\ChangeUserSettingInterface;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;

final class ChangeRgbCode implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_USER_RGB';

    public function __construct(private ChangeUserSettingInterface $changerUserSetting) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $value = request::postStringFatal('rgb_code');
        if (strlen($value) != 7) {
            $game->getInfo()->addInformation(_('Der RGB-Code muss sieben Zeichen lang sein, z.B. #11ff67'));
            return;
        }

        if (!$this->validHex($value)) {
            $game->getInfo()->addInformation(_('Der RGB-Code ist ungültig!'));
            return;
        }

        $this->changerUserSetting->change(
            $game->getUser(),
            UserSettingEnum::RGB_CODE,
            $value
        );

        $game->getInfo()->addInformation(_('Dein RGB-Code wurde geändert'));
    }

    private function validHex(string $hex): int|bool
    {
        return preg_match('/^#?(([a-f0-9]{3}){1,2})$/i', $hex);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
