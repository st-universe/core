<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Module\PlayerSetting\Lib\UserSettingWrapper;

final class PlayerSettingsProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private readonly UserSettingsProviderInterface $userSettingsProvider
    ) {}

    #[\Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->appendNavigationPart(
            'options.php',
            _('Optionen')
        );

        $game->setTemplateVar('DISTINCT_SETTING_WRAPPERS', array_map(fn (UserSettingEnum $type): UserSettingWrapper => new UserSettingWrapper($user, $type, $game->isAdmin(), $this->userSettingsProvider), UserSettingEnum::getDistinct()));
        $game->setTemplateVar('SETTING_WRAPPERS', array_map(fn (UserSettingEnum $type): UserSettingWrapper => new UserSettingWrapper($user, $type, $game->isAdmin(), $this->userSettingsProvider), UserSettingEnum::getNonDistinct()));

        $game->setTemplateVar('REAL_USER', $user);
    }
}
