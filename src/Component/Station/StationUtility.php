<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use RuntimeException;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Action\BuildConstruction\BuildConstruction;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Repository\ConstructionProgressModuleRepositoryInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

//TODO unit tests
final class StationUtility implements StationUtilityInterface
{
    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ConstructionProgressRepositoryInterface $constructionProgressRepository;

    private ConstructionProgressModuleRepositoryInterface $constructionProgressModuleRepository;

    private ShipCreatorInterface $shipCreator;

    private ShipRepositoryInterface $shipRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private LoggerUtilInterface $loggerUtil;

    private TradePostRepositoryInterface $tradePostRepository;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ConstructionProgressRepositoryInterface $constructionProgressRepository,
        ConstructionProgressModuleRepositoryInterface $constructionProgressModuleRepository,
        ShipCreatorInterface $shipCreator,
        ShipRepositoryInterface $shipRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipRumpRepositoryInterface $shipRumpRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        TradePostRepositoryInterface $tradePostRepository,
        TradeLicenseRepositoryInterface $tradeLicenseRepository
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->constructionProgressRepository = $constructionProgressRepository;
        $this->constructionProgressModuleRepository = $constructionProgressModuleRepository;
        $this->shipCreator = $shipCreator;
        $this->shipRepository = $shipRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->tradePostRepository = $tradePostRepository;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
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
            if (
                !$ship->getStorage()->containsKey($key)
                || $ship->getStorage()->get($key)->getAmount() < $amount
            ) {
                return false;
            }
        }

        return true;
    }

    public function getStationBuildplansByUser(int $userId): array
    {
        return $this->shipBuildplanRepository->getStationBuildplansByUser($userId);
    }

    public function getShipyardBuildplansByUser(int $userId): array
    {
        return $this->shipBuildplanRepository->getShipyardBuildplansByUser($userId);
    }

    public function getBuidplanIfResearchedByUser(int $planId, int $userId): ?ShipBuildplanInterface
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

    public function getDockedWorkbeeCount(ShipInterface $ship): int
    {
        $dockedWorkbees = 0;
        foreach ($ship->getDockedShips() as $docked) {
            if (!$docked->hasEnoughCrew()) {
                continue;
            }

            $commodity = $docked->getRump()->getCommodity();
            if ($commodity !== null && $commodity->isWorkbee()) {
                $dockedWorkbees += 1;
            }
        }

        return $dockedWorkbees;
    }

    public function getNeededWorkbeeCount(ShipInterface $station, ShipRumpInterface $rump): int
    {
        if ($rump->getNeededWorkbees() === null) {
            return 0;
        }

        switch ($station->getState()) {
            case ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION:
                return $rump->getNeededWorkbees();
            case ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING:
                return (int)ceil($rump->getNeededWorkbees() / 2);
            case ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE:
                return (int)ceil($rump->getNeededWorkbees() / 5);
            default:
                throw new RuntimeException(sprintf(
                    'shipState %d of shipId %d is not supported',
                    $station->getState(),
                    $station->getId()
                ));
        }
    }

    public function hasEnoughDockedWorkbees(ShipInterface $station, ShipRumpInterface $rump): bool
    {
        return $this->getDockedWorkbeeCount($station) >= $this->getNeededWorkbeeCount($station, $rump);
    }

    public function getConstructionProgress(ShipInterface $ship): ?ConstructionProgressInterface
    {
        return $this->constructionProgressRepository->getByShip($ship->getId());
    }

    public function reduceRemainingTicks(ConstructionProgressInterface $progress): void
    {
        $progress->setRemainingTicks($progress->getRemainingTicks() - 1);
        $this->constructionProgressRepository->save($progress);
    }

    public function finishStation(ShipInterface $ship, ConstructionProgressInterface $progress): void
    {
        $plan = $ship->getBuildplan();
        $rump = $ship->getRump();

        // transform ship
        $wrapper = $this->shipCreator->createBy($ship->getUser()->getId(), $rump->getId(), $plan->getId(), null, $progress);
        $station = $wrapper->get();

        // set influence area
        if ($station->getRump()->getShipRumpRole()->getId() === ShipRumpEnum::SHIP_ROLE_BASE) {
            $station->setInfluenceArea($station->getMap()->getSystem());
            $this->shipRepository->save($station);
        }

        // make tradepost entry
        if ($station->getRump()->getShipRumpRole()->getId() === ShipRumpEnum::SHIP_ROLE_OUTPOST) {
            $this->createTradepostAndLicense($station);
        }

        // set progress finished
        $progress->setRemainingTicks(0);
        $this->constructionProgressRepository->save($progress);
    }

    private function createTradepostAndLicense(ShipInterface $station)
    {
        $owner = $station->getUser();
        $tradepost = $this->tradePostRepository->prototype();
        $tradepost->setUser($owner);
        $tradepost->setName('Handelsposten');
        $tradepost->setDescription('Privater Handelsposten');
        $tradepost->setShip($station);
        $tradepost->setTradeNetwork($owner->getId());
        $tradepost->setLevel((int) 1);
        $tradepost->setTransferCapacity((int) 0);
        $tradepost->setStorage((int) 10000);
        $this->tradePostRepository->save($tradepost);

        $station->setTradePost($tradepost);
        $this->shipRepository->save($station);

        $license = $this->tradeLicenseRepository->prototype();
        $license->setTradePost($tradepost);
        $license->setUser($owner);
        $license->setDate(time());
        $license->setExpired(2147483647); // 2147483647 = maxInt in postgres:  19. January 2038

        $this->tradeLicenseRepository->save($license);
    }

    public function finishScrapping(ShipInterface $station, ConstructionProgressInterface $progress): void
    {
        // transform to construction
        $rumpId = $station->getUser()->getFactionId() + ShipRumpEnum::SHIP_RUMP_BASE_ID_CONSTRUCTION;
        $rump = $this->shipRumpRepository->find($rumpId);

        $station->setState(ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION);
        $station->setBuildplan(null);
        $station->setRump($rump);
        $station->setName($rump->getName());
        $station->setHuell($rump->getBaseHull());
        $station->setMaxHuell($rump->getBaseHull());

        $this->shipRepository->save($station);

        // salvage modules
        foreach ($progress->getSpecialModules() as $progressModule) {
            $this->shipStorageManager->upperStorage(
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

    public function canManageShips(ShipInterface $ship): bool
    {
        return $ship->getRump()->getShipRumpRole() !== null
            && ($ship->getRump()->getShipRumpRole()->getId() === ShipRumpEnum::SHIP_ROLE_OUTPOST
                || $ship->getRump()->getShipRumpRole()->getId() === ShipRumpEnum::SHIP_ROLE_BASE)
            && $ship->hasEnoughCrew();
    }

    public function canRepairShips(ShipInterface $ship): bool
    {
        return $ship->getRump()->getShipRumpRole() !== null
            && ($ship->getRump()->getShipRumpRole()->getId() === ShipRumpEnum::SHIP_ROLE_SHIPYARD
                || $ship->getRump()->getShipRumpRole()->getId() === ShipRumpEnum::SHIP_ROLE_BASE)
            && $ship->hasEnoughCrew();
    }
}
