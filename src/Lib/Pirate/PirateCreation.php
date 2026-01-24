<?php

namespace Stu\Lib\Pirate;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Stu\Component\Map\MapEnum;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\PirateSetup;
use Stu\Orm\Entity\PirateRound;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\NamesRepositoryInterface;
use Stu\Orm\Repository\PirateRoundRepositoryInterface;
use Stu\Orm\Repository\PirateSetupRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class PirateCreation implements PirateCreationInterface
{
    public const MAX_PIRATE_FLEETS = 10;
    public const MAX_PIRATE_FLEETS_PER_TICK = 5;
    public const MAX_PIRATE_FLEETS_PER_10MIN = 3;

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
        private PirateRoundRepositoryInterface $pirateRoundRepository,
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

    #[\Override]
    public function createPirateFleetsIfNeeded(): array
    {
        $currentRound = $this->pirateRoundRepository->getCurrentActiveRound();

        if ($currentRound === null) {
            $this->logger->log('    Keine aktive Piratenrunde - keine neuen Flotten');
            return [];
        }

        if ($currentRound->getActualPrestige() <= 0) {
            $this->logger->log('    Prestige auf 0 - keine neuen Piratenflotten');
            return [];
        }

        $gameTurn = $this->game->getCurrentRound();
        $pirateFleets = $this->fleetRepository->getByUser(UserConstants::USER_NPC_KAZON);
        $currentFleetCount = count($pirateFleets);

        $dynamicLimits = $this->calculateDynamicLimits($currentRound);

        $this->logger->logf(
            '    Dynamische Limits: Max=%d, PerTick=%d, Per10Min=%d',
            $dynamicLimits['maxFleets'],
            $dynamicLimits['maxPerTick'],
            $dynamicLimits['maxPer10Min']
        );

        $missingFleetAmount = min(
            max(0, $dynamicLimits['maxPerTick'] - $gameTurn->getPirateFleets()),
            max(0, $dynamicLimits['maxFleets'] - $currentFleetCount)
        );

        if ($missingFleetAmount <= 0) {
            $this->logger->logf(
                '    Tick-Limit erreicht (%d/%d) oder max. Flotten erreicht (%d/%d)',
                $gameTurn->getPirateFleets(),
                $dynamicLimits['maxPerTick'],
                $currentFleetCount,
                $dynamicLimits['maxFleets']
            );
            return $pirateFleets;
        }

        $spawnProbability = $this->calculateSpawnProbability($currentRound);

        $this->logger->logf(
            '    Spawn-Wahrscheinlichkeit: %.2f%% (Prestige: %d/%d)',
            $spawnProbability * 100,
            $currentRound->getActualPrestige(),
            $currentRound->getMaxPrestige()
        );

        $randomValue = $this->stuRandom->rand(1, 100);
        $spawnThreshold = $spawnProbability * 100;

        if ($randomValue > $spawnThreshold) {
            $this->logger->logf(
                '    Spawn-Wahrscheinlichkeit nicht erreicht (Random: %d, Threshold: %.2f)',
                $randomValue,
                $spawnThreshold
            );
            return $pirateFleets;
        }


        /** @var int<0, max> $maxNewFleets */
        $maxNewFleets = min($dynamicLimits['maxPer10Min'], $missingFleetAmount);

        if ($maxNewFleets <= 0) {
            return $pirateFleets;
        }

        $fleetsToSpawn = $this->stuRandom->rand(1, $maxNewFleets);

        $this->logger->logf('    Spawne %d neue Piratenflotten', $fleetsToSpawn);

        for ($i = 0; $i < $fleetsToSpawn; $i++) {
            $this->logger->logf('  Flotte Nr. %d', $i + 1);
            $pirateFleets[] = $this->createPirateFleet();
            $gameTurn->setPirateFleets($gameTurn->getPirateFleets() + 1);
        }

        $this->gameTurnRepository->save($gameTurn);

        return $pirateFleets;
    }

    /**
     * 
     * @return array{maxFleets: int, maxPerTick: int, maxPer10Min: int}
     */
    private function calculateDynamicLimits(PirateRound $currentRound): array
    {
        $maxPrestige = $currentRound->getMaxPrestige();
        $actualPrestige = $currentRound->getActualPrestige();

        if ($maxPrestige <= 0) {
            return [
                'maxFleets' => 1,
                'maxPerTick' => 1,
                'maxPer10Min' => 1
            ];
        }

        $consumedRatio = 1.0 - ($actualPrestige / $maxPrestige);
        $scalingFactor = $this->calculateLimitScalingFactor($consumedRatio);

        $this->logger->logf(
            '    Prestige-Verbrauch: %.1f%%, Skalierungsfaktor: %.2f',
            $consumedRatio * 100,
            $scalingFactor
        );

        return [
            'maxFleets' => max(1, (int) round(self::MAX_PIRATE_FLEETS * $scalingFactor)),
            'maxPerTick' => max(1, (int) round(self::MAX_PIRATE_FLEETS_PER_TICK * $scalingFactor)),
            'maxPer10Min' => max(1, (int) round(self::MAX_PIRATE_FLEETS_PER_10MIN * $scalingFactor))
        ];
    }

    private function calculateLimitScalingFactor(float $consumedRatio): float
    {
        if ($consumedRatio <= 0.2) {
            return 0.1 + (0.9 * ($consumedRatio / 0.2));
        } elseif ($consumedRatio <= 0.7) {
            return 1.0;
        } else {
            $remainingRatio = (1.0 - $consumedRatio) / 0.3;
            return 0.25 + (0.75 * $remainingRatio);
        }
    }

    private function calculateSpawnProbability(PirateRound $currentRound): float
    {
        $maxPrestige = $currentRound->getMaxPrestige();
        $actualPrestige = $currentRound->getActualPrestige();

        if ($maxPrestige <= 0) {
            return 0.1;
        }

        $consumedRatio = 1.0 - ($actualPrestige / $maxPrestige);

        if ($consumedRatio <= 0.2) {
            return 0.15 + (0.55 * ($consumedRatio / 0.2));
        } elseif ($consumedRatio <= 0.7) {
            return 0.85;
        } else {
            $remainingRatio = (1.0 - $consumedRatio) / 0.3;
            return 0.25 + (0.6 * $remainingRatio);
        }
    }

    #[\Override]
    public function createPirateFleet(?Spacecraft $supportCaller = null): Fleet
    {
        $pirateUser = $this->userRepository->find(UserConstants::USER_NPC_KAZON);
        if ($pirateUser === null) {
            throw new RuntimeException('this should not happen');
        }

        $pirateSetup = $this->getRandomPirateSetup();

        //create ships
        $ships = $this->createShips($pirateSetup, $supportCaller);
        $this->entityManager->flush();

        $fleetLeader = $ships[$this->stuRandom->array_rand($ships)];
        $fleetLeader->setIsFleetLeader(true);

        //create fleet
        $fleet = $this->fleetRepository->prototype();
        $fleet->setUser($pirateUser);
        $fleet->setName($pirateSetup->getName());
        $fleet->setIsFleetFixed(true);
        $fleet->setLeadShip($fleetLeader);

        $this->logger->log(sprintf('    shipCount: %d', count($ships)));

        foreach ($ships as $ship) {
            $ship->setFleet($fleet);
            $this->shipRepository->save($ship);
        }

        $this->fleetRepository->save($fleet);
        $this->entityManager->flush();

        return $fleet;
    }

    /** @return array<Ship> */
    private function createShips(PirateSetup $pirateSetup, ?Spacecraft $supportCaller): array
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
                    $selectedNameEntry = $mostUnusedNames[$this->stuRandom->array_rand($mostUnusedNames)];
                    $shipName = $selectedNameEntry->getName();
                    $selectedNameEntry->setCount($selectedNameEntry->getCount() + 1);
                    $this->namesRepository->save($selectedNameEntry);
                } else {
                    $shipName = "Pirate Ship";
                }

                $result[] = $this->shipCreator
                    ->createBy(
                        UserConstants::USER_NPC_KAZON,
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

    private function getRandomPirateSetup(): PirateSetup
    {
        $pirateSetups = $this->pirateSetupRepository->findAll();

        $pirateProbabilities = array_map(fn(PirateSetup $setup): int => $setup->getProbabilityWeight(), $pirateSetups);

        return $pirateSetups[$this->stuRandom->randomKeyOfProbabilities($pirateProbabilities)];
    }

    private function getRandomMapLocation(): Map
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
