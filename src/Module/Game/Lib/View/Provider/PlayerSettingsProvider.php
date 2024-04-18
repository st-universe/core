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


        $filteredViews = array_filter(ModuleViewEnum::cases(), function (ModuleViewEnum $case) use ($game) {

            if (in_array($case, [ModuleViewEnum::GAME, ModuleViewEnum::INDEX, ModuleViewEnum::NOTES])) {
                return false;
            }

            if ($case === ModuleViewEnum::ADMIN && !$game->isAdmin()) {
                return false;
            }

            if ($case === ModuleViewEnum::NPC && !$game->isNpc()) {
                return false;
            }
            return true;
        });



        $game->setTemplateVar('REAL_USER', $user);
        $game->setTemplateVar('VIEWS', $filteredViews);
        $game->setTemplateVar('RPG_BEHAVIOR_VALUES', UserRpgBehaviorEnum::cases());
        $game->setTemplateVar('CSS_VALUES', UserCssClassEnum::cases());
    }
}
