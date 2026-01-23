<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Action\BuildConstruction\BuildConstruction;
use Stu\Module\Station\Lib\Creation\StationCreatorInterface;
use Stu\Orm\Entity\ConstructionProgress;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Station;
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
        private StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }


    public static function canShipBuildConstruction(Ship $ship): bool
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

    #[\Override]
    public function getStationBuildplansByUser(int $userId): array
    {
        return $this->spacecraftBuildplanRepository->getStationBuildplansByUser($userId);
    }

    #[\Override]
    public function getShipyardBuildplansByUser(int $userId): array
    {
        return $this->spacecraftBuildplanRepository->getShipyardBuildplansByUser($userId);
    }

    #[\Override]
    public function getBuidplanIfResearchedByUser(int $planId, int $userId): ?SpacecraftBuildplan
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

    #[\Override]
    public function getDockedWorkbeeCount(Station $station): int
    {
        return $station->getDockedWorkbeeCount();
    }

    #[\Override]
    public function getNeededWorkbeeCount(Station $station, SpacecraftRump $rump): int
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

    #[\Override]
    public function hasEnoughDockedWorkbees(Station $station, SpacecraftRump $rump): bool
    {
        return $this->getDockedWorkbeeCount($station) >= $this->getNeededWorkbeeCount($station, $rump);
    }

    #[\Override]
    public function reduceRemainingTicks(ConstructionProgress $progress): void
    {
        $progress->setRemainingTicks($progress->getRemainingTicks() - 1);
        $this->constructionProgressRepository->save($progress);
    }

    #[\Override]
    public function finishStation(ConstructionProgress $progress): void
    {
        $station = $progress->getStation();
        $plan = $station->getBuildplan();
        if ($plan === null) {
            throw new RuntimeException(sprintf('stationId %d does not have buildplan', $station->getId()));
        }
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
        if ($station->getRump()->getShipRumpRole()?->getId() === SpacecraftRumpRoleEnum::BASE) {
            $station->setInfluenceArea($station->getMap()?->getSystem());
            $this->stationRepository->save($station);
        }

        // make tradepost entry
        if ($station->getRump()->getShipRumpRole()?->getId() === SpacecraftRumpRoleEnum::OUTPOST) {
            $this->createTradepostAndLicense($station);
        }

        // set progress finished
        $progress->setRemainingTicks(0);
        $this->constructionProgressRepository->save($progress);
    }

    private function createTradepostAndLicense(Station $station): void
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

    #[\Override]
    public function finishScrapping(ConstructionProgress $progress, InformationInterface $information): void
    {
        $station = $progress->getStation();

        $buildplan = $station->getBuildplan();
        if ($buildplan === null) {
            return;
        }

        $rumpRoleId = $station->getRump()->getShipRumpRole()?->getId();
        if ($rumpRoleId === SpacecraftRumpRoleEnum::DEPOT_SMALL || $rumpRoleId === SpacecraftRumpRoleEnum::DEPOT_LARGE) {
            $station->setAlliance(null);
            $this->stationRepository->save($station);
        }

        $buildplanModules = $buildplan->getModules();
        $specialModules = $progress->getSpecialModules();
        $recycledModules = [];

        foreach ($buildplanModules as $buildplanModule) {
            $module = $buildplanModule->getModule();
            $moduleId = $module->getId();
            $moduleCount = $buildplanModule->getModuleCount();

            $isSpecialModule = false;
            foreach ($specialModules as $progressModule) {
                if ($progressModule->getModule()->getId() === $moduleId) {
                    $isSpecialModule = true;
                    break;
                }
            }

            if ($isSpecialModule) {
                $amount = 1;
            } else {
                if ($this->stuRandom->rand(1, 100) <= 33) {
                    $amount = 0;
                } else {
                    $amount = $this->stuRandom->rand(1, $moduleCount);
                }
            }

            if ($amount === 0) {
                continue;
            }

            $this->storageManager->upperStorage(
                $station,
                $module->getCommodity(),
                $amount
            );

            $recycledModules[] = ['module' => $module, 'amount' => $amount];
        }

        if (count($recycledModules) > 0) {
            $information->addInformation("\nFolgende Module wurden recycelt:");
            foreach ($recycledModules as $recycled) {
                $information->addInformationf(
                    '%s, Anzahl: %d',
                    $recycled['module']->getName(),
                    $recycled['amount']
                );
            }
        }

        $rumpId = $station->getUser()->getFactionId() + SpacecraftRumpEnum::SHIP_RUMP_BASE_ID_CONSTRUCTION;
        $rump = $this->spacecraftRumpRepository->find($rumpId);
        if ($rump === null) {
            throw new RuntimeException(sprintf('construction rump with id %d not found', $rumpId));
        }

        $baseHull = $rump->getBaseValues()->getBaseHull();

        $station->setBuildplan(null);
        $station->setRump($rump);
        $station->setName($rump->getName());
        $station->setMaxHull($baseHull);
        $station->getCondition()->setHull($baseHull);
        $station->getCondition()->setState(SpacecraftStateEnum::UNDER_CONSTRUCTION);

        $this->stationRepository->save($station);

        $this->constructionProgressModuleRepository->truncateByProgress($progress->getId());

        $progress->setRemainingTicks(0);
        $this->constructionProgressRepository->save($progress);
    }

    #[\Override]
    public function canManageShips(Station $station): bool
    {
        return ($station->getRump()->getShipRumpRole()?->getId() === SpacecraftRumpRoleEnum::OUTPOST
            || $station->getRump()->getShipRumpRole()?->getId() === SpacecraftRumpRoleEnum::BASE)
            && $station->hasEnoughCrew();
    }

    #[\Override]
    public function canRepairShips(Station $station): bool
    {
        return ($station->getRump()->getShipRumpRole()?->getId() === SpacecraftRumpRoleEnum::SHIPYARD
            || $station->getRump()->getShipRumpRole()?->getId() === SpacecraftRumpRoleEnum::BASE)
            && $station->hasEnoughCrew();
    }
}
