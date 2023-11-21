<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Database\View\Category\Tal\DatabaseCategoryTalFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ColonyDepositMiningInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ManagementProvider implements GuiComponentProviderInterface
{
    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory;

    private OrbitShipListRetrieverInterface $orbitShipListRetriever;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory,
        OrbitShipListRetrieverInterface $orbitShipListRetriever,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->databaseCategoryTalFactory = $databaseCategoryTalFactory;
        $this->orbitShipListRetriever = $orbitShipListRetriever;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->colonyFunctionManager = $colonyFunctionManager;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {

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
            'SURFACE',
            $this->colonyLibFactory->createColonySurface($host)
        );


        $game->setTemplateVar(
            'FIRST_ORBIT_SHIP',
            $firstOrbitShip ? $this->shipWrapperFactory->wrapShip($firstOrbitShip) : null
        );

        $particlePhalanx = $this->colonyFunctionManager->hasFunction($host, BuildingEnum::BUILDING_FUNCTION_PARTICLE_PHALANX);
        $game->setTemplateVar(
            'BUILDABLE_TORPEDO_TYPES',
            $particlePhalanx ? $this->torpedoTypeRepository->getForUser($game->getUser()->getId()) : null
        );

        $game->setTemplateVar('DEPOSIT_MININGS', $this->getUserDepositMinings($host));
    }

    /**
     * @return array<int, array{deposit: ColonyDepositMiningInterface, currentlyMined: int}>
     */
    private function getUserDepositMinings(PlanetFieldHostInterface $host): array
    {
        $production = $this->colonyLibFactory->createColonyCommodityProduction($host)->getProduction();

        $result = [];
        if (!$host instanceof ColonyInterface) {
            return $result;
        }

        foreach ($host->getDepositMinings() as $deposit) {
            if ($deposit->getUser() === $host->getUser()) {
                $prod = $production[$deposit->getCommodity()->getId()] ?? null;

                $result[$deposit->getCommodity()->getId()] = [
                    'deposit' => $deposit,
                    'currentlyMined' => $prod === null ? 0 : $prod->getProduction()
                ];
            }
        }

        return $result;
    }
}
