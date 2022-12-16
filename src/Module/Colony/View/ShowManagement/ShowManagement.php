<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowManagement;

use ColonyMenu;
use request;

use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Database\View\Category\Tal\DatabaseCategoryTalFactoryInterface;
use Stu\Module\Tal\OrbitShipItem;
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

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowManagementRequestInterface $showManagementRequest,
        ColonyLibFactoryInterface $colonyLibFactory,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showManagementRequest = $showManagementRequest;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->databaseCategoryTalFactory = $databaseCategoryTalFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showManagementRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $firstOrbitShip = null;

        $shipList = $colony->getOrbitShipList($userId);
        if ($shipList !== []) {
            // if selected, return the current target
            $target = request::postInt('target');

            if ($target) {
                foreach ($shipList as $key => $fleet) {
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
        if ($colony->getImmigration() > 0) {
            $immigrationSymbol = '+';
        }
        if ($colony->getImmigration() == 0) {
            $immigrationSymbol = '';
        }

        $game->showMacro('html/colonymacros.xhtml/cm_management');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_INFO));
        $game->setTemplateVar(
            'FIRST_ORBIT_SHIP',
            $firstOrbitShip === null ? null : new OrbitShipItem($firstOrbitShip, $game)
        );
        $game->setTemplateVar('COLONY_SURFACE', $this->colonyLibFactory->createColonySurface($colony));
        $game->setTemplateVar('IMMIGRATION_SYMBOL', $immigrationSymbol);

        $starsystem = $this->databaseCategoryTalFactory->createDatabaseCategoryEntryTal($colony->getSystem()->getDatabaseEntry(), $game->getUser());
        $game->setTemplateVar('STARSYSTEM_ENTRY_TAL', $starsystem);

        $particlePhalanxCount = $colony->getBuildingWithFunctionCount(BuildingEnum::BUILDING_FUNCTION_PARTICLE_PHALANX, [0, 1]);
        $game->setTemplateVar('BUILDABLE_TORPEDO_TYPES', $particlePhalanxCount > 0 ? $this->torpedoTypeRepository->getForUser($userId) : null);
    }
}
