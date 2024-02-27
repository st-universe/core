<?php

namespace Stu\Lib\Pirate;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\PirateSetupInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\PirateSetupRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class PirateCreation implements PirateCreationInterface
{
    public const MAX_PIRATE_FLEETS = 5;

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private PirateSetupRepositoryInterface $pirateSetupRepository;

    private ShipCreatorInterface $shipCreator;

    private LayerRepositoryInterface $layerRepository;

    private MapRepositoryInterface $mapRepository;

    private StuRandom $stuRandom;

    private EntityManagerInterface $entityManager;

    private LoggerUtilInterface $logger;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        PirateSetupRepositoryInterface $pirateSetupRepository,
        ShipCreatorInterface $shipCreator,
        LayerRepositoryInterface $layerRepository,
        MapRepositoryInterface $mapRepository,
        StuRandom $stuRandom,
        EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->pirateSetupRepository = $pirateSetupRepository;
        $this->shipCreator = $shipCreator;
        $this->layerRepository = $layerRepository;
        $this->mapRepository = $mapRepository;
        $this->stuRandom = $stuRandom;
        $this->entityManager = $entityManager;

        $this->logger = $loggerUtilFactory->getLoggerUtil();
    }

    public function createPirateFleetsIfNeeded(): array
    {
        $pirateFleets = $this->fleetRepository->getByUser(UserEnum::USER_NPC_KAZON);
        $missingFleetAmount = max(0, self::MAX_PIRATE_FLEETS - count($pirateFleets));

        $pirateUser = $this->userRepository->find(UserEnum::USER_NPC_KAZON);
        if ($pirateUser === null) {
            throw new RuntimeException('this should not happen');
        }

        for ($i = 0; $i < $missingFleetAmount; $i++) {
            $pirateFleets[] = $this->createPirateFleet($pirateUser);
        }

        return $pirateFleets;
    }

    private function createPirateFleet(UserInterface $user): FleetInterface
    {
        $pirateSetup = $this->getRandomPirateSetup();

        //create ships
        $ships = $this->createShips($pirateSetup);
        $this->entityManager->flush();

        $fleetLeader = $ships[array_rand($ships)];
        $fleetLeader->setIsFleetLeader(true);

        //create fleet
        $fleet = $this->fleetRepository->prototype();
        $fleet->setUser($user);
        $fleet->setName($pirateSetup->getName());
        $fleet->setIsFleetFixed(true);
        $fleet->setLeadShip($fleetLeader);

        $this->logger->log(sprintf('shipCount: %d', count($ships)));

        foreach ($ships as $ship) {
            $ship->setFleet($fleet);
            $this->shipRepository->save($ship);
        }

        $this->fleetRepository->save($fleet);
        $this->entityManager->flush();

        return $fleet;
    }

    /** @return array<ShipInterface> */
    private function createShips(PirateSetupInterface $pirateSetup): array
    {
        $randomLocation = $this->getRandomMapLocation();
        $randomAlertLevel = ShipAlertStateEnum::getRandomAlertLevel();

        $this->logger->log(sprintf('randomAlertLevel: %d', $randomAlertLevel->value));

        $result = [];

        foreach ($pirateSetup->getSetupBuildplans() as $setupBuildplan) {

            $buildplan = $setupBuildplan->getBuildplan();
            $rump = $buildplan->getRump();

            for ($i = 0; $i < $setupBuildplan->getAmount(); $i++) {
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

        $pirateProbabilities = array_map(fn (PirateSetupInterface $setup) => $setup->getProbabilityWeight(), $pirateSetups);

        return $pirateSetups[$this->stuRandom->randomOfProbabilities($pirateProbabilities)];
    }

    private function getRandomMapLocation(): MapInterface
    {
        $defaultLayer = $this->layerRepository->getDefaultLayer();

        $result = $this->mapRepository->getRandomPassableUnoccupiedWithoutDamage($defaultLayer, true);

        $this->logger->log(sprintf('randomMapLocation: %s', $result->getSectorString()));

        return $result;
    }
}
