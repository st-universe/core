<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\OrbitShipWrappersRetrieverInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Database\View\Category\Wrapper\DatabaseCategoryWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\ColonyDepositMiningInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ManagementProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(
        private StationRepositoryInterface $stationRepository,
        private TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        private DatabaseCategoryWrapperFactoryInterface $databaseCategoryWrapperFactory,
        private OrbitShipWrappersRetrieverInterface $orbitShipWrappersRetriever,
        private ColonyFunctionManagerInterface $colonyFunctionManager,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private StuTime $stuTime
    ) {}

    #[Override]
    public function setTemplateVariables(
        $entity,
        GameControllerInterface $game
    ): void {

        if (!$entity instanceof ColonyInterface) {
            return;
        }

        $systemDatabaseEntry = $entity->getSystem()->getDatabaseEntry();
        if ($systemDatabaseEntry !== null) {
            $starsystem = $this->databaseCategoryWrapperFactory->createDatabaseCategoryEntryWrapper($systemDatabaseEntry, $game->getUser());
            $game->setTemplateVar('STARSYSTEM_ENTRY_TAL', $starsystem);
        }

        $firstOrbitShipWrapper = null;

        $targetId = request::indInt('target');
        $groups = $this->orbitShipWrappersRetriever->retrieve($entity);

        if ($targetId !== 0) {
            foreach ($groups as $group) {
                foreach ($group->getWrappers() as $wrapper) {
                    if ($wrapper->get()->getId() === $targetId) {
                        $firstOrbitShipWrapper = $wrapper;
                    }
                }
            }
        }
        if ($firstOrbitShipWrapper === null) {
            $firstGroup = $groups->first();
            $firstOrbitShipWrapper = $firstGroup ? $firstGroup->getWrappers()->first() : null;
        }

        $game->setTemplateVar(
            'POPULATION_CALCULATOR',
            $this->colonyLibFactory->createColonyPopulationCalculator($entity)
        );

        $station = $this->stationRepository->getStationOnLocation($entity->getLocation());
        if ($station !== null) {
            $game->setTemplateVar('ORBIT_STATION_WRAPPER', $this->spacecraftWrapperFactory->wrapStation($station));
        }
        $game->setTemplateVar('FIRST_ORBIT_SPACECRAFT', $firstOrbitShipWrapper);

        $particlePhalanx = $this->colonyFunctionManager->hasFunction($entity, BuildingFunctionEnum::PARTICLE_PHALANX);
        $game->setTemplateVar(
            'BUILDABLE_TORPEDO_TYPES',
            $particlePhalanx ? $this->torpedoTypeRepository->getForUser($game->getUser()->getId()) : null
        );

        $shieldingManager = $this->colonyLibFactory->createColonyShieldingManager($entity);
        $game->setTemplateVar('SHIELDING_MANAGER', $shieldingManager);
        $game->setTemplateVar('DEPOSIT_MININGS', $this->getUserDepositMinings($entity));
        $game->setTemplateVar('VISUAL_PANEL', $this->colonyLibFactory->createColonyScanPanel($entity));

        $timestamp = $this->stuTime->time();
        $game->setTemplateVar('COLONY_TIME_HOUR', $entity->getColonyTimeHour($timestamp));
        $game->setTemplateVar('COLONY_TIME_MINUTE', $entity->getColonyTimeMinute($timestamp));
        $game->setTemplateVar('COLONY_DAY_TIME_PREFIX', $entity->getDayTimePrefix($timestamp));
        $game->setTemplateVar('COLONY_DAY_TIME_NAME', $entity->getDayTimeName($timestamp));
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
