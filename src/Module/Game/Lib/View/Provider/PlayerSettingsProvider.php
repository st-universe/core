<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Component\Player\UserCssClassEnum;
use Stu\Component\Player\UserRpgBehaviorEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;

final class PlayerSettingsProvider implements ViewComponentProviderInterface
{
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->appendNavigationPart(
            'options.php',
            _('Optionen')
        );

        $game->setTemplateVar('REAL_USER', $user);
        $game->setTemplateVar('VIEWS', ModuleViewEnum::cases());
        $game->setTemplateVar('RPG_BEHAVIOR_VALUES', UserRpgBehaviorEnum::cases());
        $game->setTemplateVar('CSS_VALUES', UserCssClassEnum::cases());
    }
}
