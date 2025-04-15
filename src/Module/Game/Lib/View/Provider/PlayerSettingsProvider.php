<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use Stu\Component\Game\ModuleEnum;
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

        $filteredViews = array_filter(ModuleEnum::cases(), function (ModuleEnum $case) use ($game): bool {

            if (in_array($case, [ModuleEnum::GAME, ModuleEnum::INDEX, ModuleEnum::NOTES])) {
                return false;
            }

            if ($case === ModuleEnum::ADMIN && !$game->isAdmin()) {
                return false;
            }
            return !($case === ModuleEnum::NPC && !$game->isNpc());
        });

        $game->setTemplateVar('REAL_USER', $user);
        $game->setTemplateVar('VIEWS', $filteredViews);
        $game->setTemplateVar('RPG_BEHAVIOR_VALUES', UserRpgBehaviorEnum::cases());
        $game->setTemplateVar('CSS_VALUES', UserCssClassEnum::cases());
    }
}
