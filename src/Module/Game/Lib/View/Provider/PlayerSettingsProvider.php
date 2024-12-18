<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Component\Player\UserCssClassEnum;
use Stu\Component\Player\UserRpgBehaviorEnum;
use Stu\Module\Control\GameControllerInterface;

final class PlayerSettingsProvider implements ViewComponentProviderInterface
{
    #[Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->appendNavigationPart(
            'options.php',
            _('Optionen')
        );

        $filteredViews = array_filter(ModuleViewEnum::cases(), function (ModuleViewEnum $case) use ($game): bool {

            if (in_array($case, [ModuleViewEnum::GAME, ModuleViewEnum::INDEX, ModuleViewEnum::NOTES])) {
                return false;
            }

            if ($case === ModuleViewEnum::ADMIN && !$game->isAdmin()) {
                return false;
            }
            return !($case === ModuleViewEnum::NPC && !$game->isNpc());
        });



        $game->setTemplateVar('REAL_USER', $user);
        $game->setTemplateVar('VIEWS', $filteredViews);
        $game->setTemplateVar('RPG_BEHAVIOR_VALUES', UserRpgBehaviorEnum::cases());
        $game->setTemplateVar('CSS_VALUES', UserCssClassEnum::cases());
    }
}
