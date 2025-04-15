<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowColony;

use Override;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Colony\PlanetFieldHostTypeEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContext;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Control\ViewWithTutorialInterface;

final class ShowColony implements ViewControllerInterface, ViewWithTutorialInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COLONY';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ColonyGuiHelperInterface $colonyGuiHelper,
        private ShowColonyRequestInterface $showColonyRequest,
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showColonyRequest->getColonyId(),
            $userId,
            false
        );

        $menu = ColonyMenuEnum::getFor($game->getViewContext(ViewContextTypeEnum::COLONY_MENU));

        $this->colonyGuiHelper->registerMenuComponents($menu, $colony, $game);
        $game->setTemplateVar('SELECTED_COLONY_MENU_TEMPLATE', ColonyMenuEnum::MENU_MAINSCREEN->getTemplate());


        if ($menu === ColonyMenuEnum::MENU_MAINSCREEN) {

            $game->setTemplateVar('SELECTED_COLONY_SUB_MENU_TEMPLATE', ColonyMenuEnum::MENU_INFO->getTemplate());
        } else {

            $game->setTemplateVar('SELECTED_COLONY_SUB_MENU_TEMPLATE', $menu->getTemplate());
            $this->colonyGuiHelper->registerComponents($colony, $game, [
                ColonyComponentEnum::SURFACE,
                ColonyComponentEnum::SHIELDING,
                ColonyComponentEnum::EPS_BAR,
                ColonyComponentEnum::STORAGE
            ]);
        }

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->appendNavigationPart(
            sprintf('?%s=1&id=%d', self::VIEW_IDENTIFIER, $colony->getId()),
            $colony->getName()
        );
        $game->setViewTemplate('html/colony/colony.twig');
        $game->setPagetitle(sprintf(_('Kolonie: %s'), $colony->getName()));

        $game->addExecuteJS(sprintf(
            "initializeJsVars(%d, %d, '%s')",
            $colony->getId(),
            PlanetFieldHostTypeEnum::COLONY->value,
            $game->getSessionString()
        ));
    }

    #[Override]
    public function getViewContext(): ViewContext
    {
        return new ViewContext(ModuleEnum::COLONY, self::VIEW_IDENTIFIER);
    }
}
