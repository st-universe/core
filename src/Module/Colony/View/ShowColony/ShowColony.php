<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowColony;

use Override;
use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Lib\Colony\PlanetFieldHostTypeEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\Gui\GuiComponentEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Tal\DatabaseCategoryTalFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ShowColony implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COLONY';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyGuiHelperInterface $colonyGuiHelper, private ShowColonyRequestInterface $showColonyRequest, private TorpedoTypeRepositoryInterface $torpedoTypeRepository, private DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory, private OrbitShipListRetrieverInterface $orbitShipListRetriever, private ColonyFunctionManagerInterface $colonyFunctionManager, private ShipWrapperFactoryInterface $shipWrapperFactory)
    {
    }

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
                GuiComponentEnum::SURFACE,
                GuiComponentEnum::SHIELDING,
                GuiComponentEnum::EPS_BAR,
                GuiComponentEnum::STORAGE
            ]);
        }


        $firstOrbitShip = null;

        $shipList = $this->orbitShipListRetriever->retrieve($colony);
        if ($shipList !== []) {
            // if selected, return the current target
            $target = request::indInt('target');

            if ($target !== 0) {
                foreach ($shipList as $fleet) {
                    foreach ($fleet['ships'] as $idx => $ship) {
                        if ($idx == $target) {
                            $firstOrbitShip = $ship;
                        }
                    }
                }
            }
            if ($firstOrbitShip === null) {
                $firstOrbitShip = current(current($shipList)['ships']);
            }
        }

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->appendNavigationPart(
            sprintf('?%s=1&id=%d', static::VIEW_IDENTIFIER, $colony->getId()),
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

        $starsystem = $this->databaseCategoryTalFactory->createDatabaseCategoryEntryTal($colony->getSystem()->getDatabaseEntry(), $user);
        $game->setTemplateVar('STARSYSTEM_ENTRY_TAL', $starsystem);

        $game->setTemplateVar('FIRST_ORBIT_SHIP', $firstOrbitShip ? $this->shipWrapperFactory->wrapShip($firstOrbitShip) : null);

        $particlePhalanx = $this->colonyFunctionManager->hasFunction($colony, BuildingEnum::BUILDING_FUNCTION_PARTICLE_PHALANX);
        $game->setTemplateVar(
            'BUILDABLE_TORPEDO_TYPES',
            $particlePhalanx ? $this->torpedoTypeRepository->getForUser($userId) : null
        );
    }
}
