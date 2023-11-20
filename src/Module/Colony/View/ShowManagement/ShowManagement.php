<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowManagement;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Tal\DatabaseCategoryTalFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ShowManagement implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MANAGEMENT';

    private PlanetFieldHostProviderInterface $planetFieldHostProvider;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory;

    private OrbitShipListRetrieverInterface $orbitShipListRetriever;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    public function __construct(
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        ColonyGuiHelperInterface $colonyGuiHelper,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory,
        OrbitShipListRetrieverInterface $orbitShipListRetriever,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->databaseCategoryTalFactory = $databaseCategoryTalFactory;
        $this->orbitShipListRetriever = $orbitShipListRetriever;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->colonyFunctionManager = $colonyFunctionManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser());

        $this->colonyGuiHelper->registerComponents($host, $game);
        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_INFO);
        $game->showMacro(ColonyMenuEnum::MENU_INFO->getTemplate());

        if (!$host instanceof ColonyInterface) {
            return;
        }

        $systemDatabaseEntry = $host->getSystem()->getDatabaseEntry();
        if ($systemDatabaseEntry !== null) {
            $starsystem = $this->databaseCategoryTalFactory->createDatabaseCategoryEntryTal($systemDatabaseEntry, $game->getUser());
            $game->setTemplateVar('STARSYSTEM_ENTRY_TAL', $starsystem);
        }

        $firstOrbitShip = null;

        $shipList = $this->orbitShipListRetriever->retrieve($host);
        if ($shipList !== []) {
            // if selected, return the current target
            $target = request::postInt('target');

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


        $game->setTemplateVar(
            'FIRST_ORBIT_SHIP',
            $firstOrbitShip ? $this->shipWrapperFactory->wrapShip($firstOrbitShip) : null
        );

        $particlePhalanx = $this->colonyFunctionManager->hasFunction($host, BuildingEnum::BUILDING_FUNCTION_PARTICLE_PHALANX);
        $game->setTemplateVar('BUILDABLE_TORPEDO_TYPES', $particlePhalanx ? $this->torpedoTypeRepository->getForUser($userId) : null);
    }
}
