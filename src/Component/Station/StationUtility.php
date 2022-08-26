<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Logging\LoggerEnum;
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
use Stu\Orm\Repository\TradePostRepositoryInterface;

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

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ConstructionProgressRepositoryInterface $constructionProgressRepository,
        ConstructionProgressModuleRepositoryInterface $constructionProgressModuleRepository,
        ShipCreatorInterface $shipCreator,
        ShipRepositoryInterface $shipRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipRumpRepositoryInterface $shipRumpRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        TradePostRepositoryInterface $tradePostRepository
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
            $commodity = $docked->getRump()->getCommodity();
            if ($commodity !== null && $commodity->isWorkbee()) {
                $dockedWorkbees += 1;
            }
        }

        return $dockedWorkbees;
    }

    public function hasEnoughDockedWorkbees(ShipInterface $ship, ShipRumpInterface $rump): bool
    {
        $isUnderConstruction = $ship->getState() === ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION;

        $neededWorkbees = $isUnderConstruction ? $rump->getNeededWorkbees() :
            (int)ceil($rump->getNeededWorkbees() / 2);

        return $this->getDockedWorkbeeCount($ship) >= $neededWorkbees;
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
        $station = $this->shipCreator->createBy($ship->getUser()->getId(), $rump->getId(), $plan->getId(), null, $progress);

        // set influence area
        if ($station->getRump()->getShipRumpRole()->getId() === ShipRumpEnum::SHIP_ROLE_BASE) {
            $station->setInfluenceArea($station->getMap()->getSystem());
            $this->shipRepository->save($station);
        }

        // make tradepost entry
        if ($station->getRump()->getShipRumpRole()->getId() === ShipRumpEnum::SHIP_ROLE_OUTPOST) {
            $tradepost = $this->tradePostRepository->prototype();
            $tradepost->setUserId($ship->getUser()->getId());
            $tradepost->setName('Handelsposten');
            $tradepost->setDescription('Privater Handelsposten');
            $tradepost->setShipId($ship);
            $tradepost->setTradeNetwork($ship->getUser()->getId());
            $tradepost->setLevel((int) 1);
            $tradepost->setTransferCapacity((int) 0);
            $tradepost->setStorage((int) 10000);
            $this->tradePostRepository->save($tradepost);

            //$station->setTradePostId($this->tradePostRepository->getTradePostIdByShip($ship->getId()));
            //$this->shipRepository->save($station);
        }

        // set progress finished
        $progress->setRemainingTicks(0);
        $this->constructionProgressRepository->save($progress);
    }

    public function finishScrapping(ShipInterface $station, ConstructionProgressInterface $progress): void
    {
        // transform to construction
        $rumpId = $station->getUser()->getFactionId() + ShipRumpEnum::SHIP_RUMP_BASE_ID_CONSTRUCTION;
        $rump = $this->shipRumpRepository->find($rumpId);

        $station->setState(ShipStateEnum::SHIP_STATE_NONE);
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

    public function getManageableShipList(ShipInterface $station): array
    {
        $result = [];

        $shiplist = $station->getDockedShips();

        foreach ($shiplist as $obj) {
            if ($obj === $station) {
                continue;
            }
            if ($obj->getWarpState()) {
                continue;
            }
            $result[$obj->getFleetId()]['ships'][$obj->getId()] = $obj;
            if (!array_key_exists('name', $result[$obj->getFleetId()])) {
                if ($obj->getFleetId() == 0) {
                    $result[$obj->getFleetId()]['name'] = _('Einzelschiffe');
                } else {
                    $result[$obj->getFleetId()]['name'] = $obj->getFleet()->getName();
                }
            }
        }
        return $result;
    }
}