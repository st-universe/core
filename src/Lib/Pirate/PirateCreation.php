<?php

namespace Stu\Lib\Pirate;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use RuntimeException;
use Stu\Component\Map\MapEnum;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\PirateSetupInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\NamesRepositoryInterface;
use Stu\Orm\Repository\PirateSetupRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class PirateCreation implements PirateCreationInterface
{
    public const MAX_PIRATE_FLEETS = 5;
    public const MAX_PIRATE_FLEETS_PER_TICK = 5;

    public const FORBIDDEN_ADMIN_AREAS = [
        MapEnum::ADMIN_REGION_SUPERPOWER_CENTRAL,
        MapEnum::ADMIN_REGION_SUPERPOWER_PERIPHERAL
    ];

    private PirateLoggerInterface $logger;

    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        private ShipRepositoryInterface $shipRepository,
        private UserRepositoryInterface $userRepository,
        private PirateSetupRepositoryInterface $pirateSetupRepository,
        private GameTurnRepositoryInterface $gameTurnRepository,
        private ShipCreatorInterface $shipCreator,
        private LayerRepositoryInterface $layerRepository,
        private MapRepositoryInterface $mapRepository,
        private GameControllerInterface $game,
        private StuRandom $stuRandom,
        private EntityManagerInterface $entityManager,
        private NamesRepositoryInterface $namesRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[Override]
    public function createPirateFleetsIfNeeded(): array
    {
        $gameTurn = $this->game->getCurrentRound();
        $pirateFleets = $this->fleetRepository->getByUser(UserEnum::USER_NPC_KAZON);

        $missingFleetAmount = min(
            max(0, self::MAX_PIRATE_FLEETS_PER_TICK - $gameTurn->getPirateFleets()),
            max(0, self::MAX_PIRATE_FLEETS - count($pirateFleets))
        );

        if ($missingFleetAmount > 0) {
            $this->logger->logf('    creating %d new needed pirate fleets', $missingFleetAmount);
        }

        for ($i = 0; $i < $missingFleetAmount; $i++) {
            $this->logger->logf('  fleet nr. %d', $i);
            $pirateFleets[] = $this->createPirateFleet();
            $gameTurn->setPirateFleets($gameTurn->getPirateFleets() + 1);
        }

        $this->gameTurnRepository->save($gameTurn);

        return $pirateFleets;
    }

    #[Override]
    public function createPirateFleet(?ShipInterface $supportCaller = null): FleetInterface
    {
        $pirateUser = $this->userRepository->find(UserEnum::USER_NPC_KAZON);
        if ($pirateUser === null) {
            throw new RuntimeException('this should not happen');
        }

        $pirateSetup = $this->getRandomPirateSetup();

        //create ships
        $ships = $this->createShips($pirateSetup, $supportCaller);
        $this->entityManager->flush();

        $fleetLeader = $ships[array_rand($ships)];
        $fleetLeader->setIsFleetLeader(true);

        //create fleet
        $fleet = $this->fleetRepository->prototype();
        $fleet->setUser($pirateUser);
        $fleet->setName($pirateSetup->getName());
        $fleet->setIsFleetFixed(true);
        $fleet->setLeadShip($fleetLeader);

        $this->logger->log(sprintf('    shipCount: %d', count($ships)));

        foreach ($ships as $ship) {
            $fleet->getShips()->add($ship);
            $ship->setFleet($fleet);
            $this->shipRepository->save($ship);
        }

        $this->fleetRepository->save($fleet);
        $this->entityManager->flush();

        return $fleet;
    }

    /** @return array<ShipInterface> */
    private function createShips(PirateSetupInterface $pirateSetup, ?ShipInterface $supportCaller): array
    {
        $randomLocation = $supportCaller === null ? $this->getRandomMapLocation() : $supportCaller->getLocation();

        $randomAlertLevel = SpacecraftAlertStateEnum::getRandomAlertLevel();

        $this->logger->log(sprintf('    randomAlertLevel: %d', $randomAlertLevel->value));

        $result = [];

        foreach ($pirateSetup->getSetupBuildplans() as $setupBuildplan) {

            $buildplan = $setupBuildplan->getBuildplan();
            $rump = $buildplan->getRump();

            for ($i = 0; $i < $setupBuildplan->getAmount(); $i++) {

                $mostUnusedNames = $this->namesRepository->mostUnusedNames();
                if ($mostUnusedNames !== []) {
                    $selectedNameEntry = $mostUnusedNames[array_rand($mostUnusedNames)];
                    $shipName = $selectedNameEntry->getName();
                    $selectedNameEntry->setCount($selectedNameEntry->getCount() + 1);
                    $this->namesRepository->save($selectedNameEntry);
                } else {
                    $shipName = "Pirate Ship";
                }


                $result[] = $this->shipCreator
                    ->createBy(
                        UserEnum::USER_NPC_KAZON,
                        $rump->getId(),
                        $buildplan->getId()
                    )
                    ->setLocation($randomLocation)
                    ->maxOutSystems()
                    ->createCrew()
                    ->setTorpedo()
                    ->setSpacecraftName($shipName)
                    ->setAlertState($randomAlertLevel)
                    ->finishConfiguration()
                    ->get();
            }
        }

        return $result;
    }

    private function getRandomPirateSetup(): PirateSetupInterface
    {
        $pirateSetups = $this->pirateSetupRepository->findAll();

        $pirateProbabilities = array_map(fn(PirateSetupInterface $setup): int => $setup->getProbabilityWeight(), $pirateSetups);

        return $pirateSetups[$this->stuRandom->randomKeyOfProbabilities($pirateProbabilities)];
    }

    private function getRandomMapLocation(): MapInterface
    {
        $defaultLayer = $this->layerRepository->getDefaultLayer();

        do {
            $map = $this->mapRepository->getRandomPassableUnoccupiedWithoutDamage($defaultLayer);
        } while (
            in_array($map->getAdminRegionId(), self::FORBIDDEN_ADMIN_AREAS)
            || $map->getFieldType()->hasEffect(FieldTypeEffectEnum::NO_PIRATES)
        );

        $this->logger->log(sprintf('    randomMapLocation: %s', $map->getSectorString()));

        return $map;
    }
}
