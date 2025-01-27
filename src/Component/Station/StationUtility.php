<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Action\BuildConstruction\BuildConstruction;
use Stu\Module\Station\Lib\Creation\StationCreatorInterface;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\ConstructionProgressModuleRepositoryInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

//TODO unit tests
final class StationUtility implements StationUtilityInterface
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private ConstructionProgressRepositoryInterface $constructionProgressRepository,
        private ConstructionProgressModuleRepositoryInterface $constructionProgressModuleRepository,
        private StationCreatorInterface $stationCreator,
        private StationRepositoryInterface $stationRepository,
        private StorageManagerInterface $storageManager,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private TradePostRepositoryInterface $tradePostRepository,
        private TradeLicenseRepositoryInterface $tradeLicenseRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public static function canShipBuildConstruction(ShipInterface $ship): bool
    {
        if (!$ship->isShuttleRampHealthy()) {
            return false;
        }

        // check if ship has the required workbee amount
        $workbeeCount = 0;
        foreach ($ship->getStorage() as $stor) {
            if ($stor->getCommodity()->isWorkbee()) {
                $workbeeCount += $stor->getAmount();
            }
        }
        if ($workbeeCount < BuildConstruction::NEEDED_WORKBEES) {
            return false;
        }

        // check if ship has the needed resources
        foreach (BuildConstruction::NEEDED_RESOURCES as $key => $amount) {
            $storage = $ship->getStorage()->get($key);
            if (
                $storage === null
                || $storage->getAmount() < $amount
            ) {
                return false;
            }
        }

        return true;
    }

    #[Override]
    public function getStationBuildplansByUser(int $userId): array
    {
        return $this->spacecraftBuildplanRepository->getStationBuildplansByUser($userId);
    }

    #[Override]
    public function getShipyardBuildplansByUser(int $userId): array
    {
        return $this->spacecraftBuildplanRepository->getShipyardBuildplansByUser($userId);
    }

    #[Override]
    public function getBuidplanIfResearchedByUser(int $planId, int $userId): ?SpacecraftBuildplanInterface
    {
        $this->loggerUtil->log(sprintf('getBuidplanIfResearchedByUser. planId: %d, userId: %d', $planId, $userId));

        $plans = $this->getStationBuildplansByUser($userId);

        foreach ($plans as $plan) {
            $this->loggerUtil->log(sprintf('planId: %d', $plan->getId()));

            if ($plan->getId() === $planId) {
                return $plan;
            }
        }

        return null;
    }

    #[Override]
    public function getDockedWorkbeeCount(StationInterface $station): int
    {
        return $station->getDockedShips()
            ->filter(fn(ShipInterface $docked): bool => $docked->hasEnoughCrew()
                && !$docked->getUser()->isVacationRequestOldEnough()
                && $docked->getRump()->isWorkbee())
            ->count();
    }

    #[Override]
    public function getNeededWorkbeeCount(StationInterface $station, SpacecraftRumpInterface $rump): int
    {
        if ($rump->getNeededWorkbees() === null) {
            return 0;
        }

        switch ($station->getState()) {
            case SpacecraftStateEnum::UNDER_CONSTRUCTION:
                return $rump->getNeededWorkbees();
            case SpacecraftStateEnum::UNDER_SCRAPPING:
                return (int)ceil($rump->getNeededWorkbees() / 2);
            case SpacecraftStateEnum::REPAIR_PASSIVE:
                return (int)ceil($rump->getNeededWorkbees() / 5);
            default:
                throw new RuntimeException(sprintf(
                    'shipState %d of shipId %d is not supported',
                    $station->getState()->value,
                    $station->getId()
                ));
        }
    }

    #[Override]
    public function hasEnoughDockedWorkbees(StationInterface $station, SpacecraftRumpInterface $rump): bool
    {
        return $this->getDockedWorkbeeCount($station) >= $this->getNeededWorkbeeCount($station, $rump);
    }

    #[Override]
    public function reduceRemainingTicks(ConstructionProgressInterface $progress): void
    {
        $progress->setRemainingTicks($progress->getRemainingTicks() - 1);
        $this->constructionProgressRepository->save($progress);
    }

    #[Override]
    public function finishStation(ConstructionProgressInterface $progress): void
    {
        $station = $progress->getStation();
        $plan = $station->getBuildplan();
        $rump = $station->getRump();

        // transform ship
        $station = $this->stationCreator
            ->createBy(
                $station->getUser()->getId(),
                $rump->getId(),
                $plan->getId(),
                $progress
            )
            ->finishConfiguration()
            ->get();

        // set influence area
        if ($station->getRump()->getShipRumpRole()->getId() === SpacecraftRumpEnum::SHIP_ROLE_BASE) {
            $station->setInfluenceArea($station->getMap()->getSystem());
            $this->stationRepository->save($station);
        }

        // make tradepost entry
        if ($station->getRump()->getShipRumpRole()->getId() === SpacecraftRumpEnum::SHIP_ROLE_OUTPOST) {
            $this->createTradepostAndLicense($station);
        }

        // set progress finished
        $progress->setRemainingTicks(0);
        $this->constructionProgressRepository->save($progress);
    }

    private function createTradepostAndLicense(StationInterface $station): void
    {
        $owner = $station->getUser();
        $tradepost = $this->tradePostRepository->prototype();
        $tradepost->setUser($owner);
        $tradepost->setName('Handelsposten');
        $tradepost->setDescription('Privater Handelsposten');
        $tradepost->setStation($station);
        $tradepost->setTradeNetwork($owner->getId());
        $tradepost->setLevel(1);
        $tradepost->setTransferCapacity(0);
        $tradepost->setStorage(10000);
        $this->tradePostRepository->save($tradepost);

        $station->setTradePost($tradepost);
        $this->stationRepository->save($station);

        $license = $this->tradeLicenseRepository->prototype();
        $license->setTradePost($tradepost);
        $license->setUser($owner);
        $license->setDate(time());
        $license->setExpired(2_147_483_647); // 2147483647 = maxInt in postgres:  19. January 2038

        $this->tradeLicenseRepository->save($license);
    }

    #[Override]
    public function finishScrapping(ConstructionProgressInterface $progress): void
    {
        $station = $progress->getStation();

        // transform to construction
        $rumpId = $station->getUser()->getFactionId() + SpacecraftRumpEnum::SHIP_RUMP_BASE_ID_CONSTRUCTION;
        $rump = $this->spacecraftRumpRepository->find($rumpId);
        if ($rump === null) {
            throw new RuntimeException(sprintf('construction rump with id %d not found', $rumpId));
        }

        $station->setState(SpacecraftStateEnum::UNDER_CONSTRUCTION);
        $station->setBuildplan(null);
        $station->setRump($rump);
        $station->setName($rump->getName());
        $station->setHuell($rump->getBaseHull());
        $station->setMaxHuell($rump->getBaseHull());

        $this->stationRepository->save($station);

        // salvage modules
        foreach ($progress->getSpecialModules() as $progressModule) {
            $this->storageManager->upperStorage(
                $station,
                $progressModule->getModule()->getCommodity(),
                1
            );
        }

        // delete progress modules
        $this->constructionProgressModuleRepository->truncateByProgress($progress->getId());

        // set progress finished
        $progress->setRemainingTicks(0);
        $this->constructionProgressRepository->save($progress);
    }

    #[Override]
    public function canManageShips(StationInterface $station): bool
    {
        return $station->getRump()->getShipRumpRole() !== null
            && ($station->getRump()->getShipRumpRole()->getId() === SpacecraftRumpEnum::SHIP_ROLE_OUTPOST
                || $station->getRump()->getShipRumpRole()->getId() === SpacecraftRumpEnum::SHIP_ROLE_BASE)
            && $station->hasEnoughCrew();
    }

    #[Override]
    public function canRepairShips(StationInterface $station): bool
    {
        return $station->getRump()->getShipRumpRole() !== null
            && ($station->getRump()->getShipRumpRole()->getId() === SpacecraftRumpEnum::SHIP_ROLE_SHIPYARD
                || $station->getRump()->getShipRumpRole()->getId() === SpacecraftRumpEnum::SHIP_ROLE_BASE)
            && $station->hasEnoughCrew();
    }
}
