<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowManagement;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\ColonyMenu;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Tal\DatabaseCategoryTalFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ShowManagement implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MANAGEMENT';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowManagementRequestInterface $showManagementRequest;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory;

    private OrbitShipListRetrieverInterface $orbitShipListRetriever;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowManagementRequestInterface $showManagementRequest,
        ColonyLibFactoryInterface $colonyLibFactory,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory,
        OrbitShipListRetrieverInterface $orbitShipListRetriever,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showManagementRequest = $showManagementRequest;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->databaseCategoryTalFactory = $databaseCategoryTalFactory;
        $this->orbitShipListRetriever = $orbitShipListRetriever;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->colonyFunctionManager = $colonyFunctionManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showManagementRequest->getColonyId(),
            $userId,
            false
        );

        $this->colonyGuiHelper->register($colony, $game);

        $surface = $this->colonyLibFactory->createColonySurface($colony);
        $populationGrowth = $surface->getPopulation()->getGrowth();

        $firstOrbitShip = null;

        $shipList = $this->orbitShipListRetriever->retrieve($colony);
        if (!empty($shipList)) {
            // if selected, return the current target
            $target = request::postInt('target');

            if ($target) {
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

        $immigrationSymbol = '-';
        if ($populationGrowth > 0) {
            $immigrationSymbol = '+';
        }
        if ($populationGrowth == 0) {
            $immigrationSymbol = '';
        }

        $game->showMacro('html/colonymacros.xhtml/cm_management');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_INFO));
        $game->setTemplateVar(
            'FIRST_ORBIT_SHIP',
            !$firstOrbitShip ? null : $this->shipWrapperFactory->wrapShip($firstOrbitShip)
        );
        $game->setTemplateVar('COLONY_SURFACE', $surface);
        $game->setTemplateVar('IMMIGRATION_SYMBOL', $immigrationSymbol);

        $systemDatabaseEntry = $colony->getSystem()->getDatabaseEntry();
        if ($systemDatabaseEntry !== null) {
            $starsystem = $this->databaseCategoryTalFactory->createDatabaseCategoryEntryTal($systemDatabaseEntry, $game->getUser());
            $game->setTemplateVar('STARSYSTEM_ENTRY_TAL', $starsystem);
        }

        $particlePhalanx = $this->colonyFunctionManager->hasFunction($colony, BuildingEnum::BUILDING_FUNCTION_PARTICLE_PHALANX);
        $game->setTemplateVar('BUILDABLE_TORPEDO_TYPES', $particlePhalanx ? $this->torpedoTypeRepository->getForUser($userId) : null);

        $game->setTemplateVar(
            'SHIELDING_MANAGER',
            $this->colonyLibFactory->createColonyShieldingManager($colony)
        );
    }
}
