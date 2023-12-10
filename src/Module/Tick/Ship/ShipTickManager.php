<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Ship;

use Stu\Component\Anomaly\AnomalyHandlingInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Tick\AbstractTickManager;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Module\Tick\Ship\Crew\CrewLimitationsInterface;
use Stu\Module\Tick\Ship\Repair\RepairActionsInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Ubench;

final class ShipTickManager extends AbstractTickManager implements ShipTickManagerInterface
{
    private CrewLimitationsInterface $crewLimitations;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRemoverInterface $shipRemover;

    private ShipTickInterface $shipTick;

    private ShipRepositoryInterface $shipRepository;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private EntryCreatorInterface $entryCreator;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private RepairActionsInterface $repairActions;

    private AnomalyHandlingInterface $anomalyHandling;

    private LockManagerInterface $lockManager;

    private LoggerUtilInterface $loggerUtil;

    private Ubench $benchmark;

    public function __construct(
        CrewLimitationsInterface $crewLimitations,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRemoverInterface $shipRemover,
        ShipTickInterface $shipTick,
        ShipRepositoryInterface $shipRepository,
        ShipCrewRepositoryInterface $shipCrewRepository,
        TradePostRepositoryInterface $tradePostRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        EntryCreatorInterface $entryCreator,
        ColonyLibFactoryInterface $colonyLibFactory,
        RepairActionsInterface $repairActions,
        AnomalyHandlingInterface $anomalyHandling,
        LockManagerInterface $lockManager,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        Ubench $benchmark
    ) {
        $this->crewLimitations = $crewLimitations;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRemover = $shipRemover;
        $this->shipTick = $shipTick;
        $this->shipRepository = $shipRepository;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->entryCreator = $entryCreator;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->repairActions = $repairActions;
        $this->anomalyHandling = $anomalyHandling;
        $this->lockManager = $lockManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->benchmark = $benchmark;
    }

    public function work(): void
    {
        $this->setLock(1);

        try {
            $this->anomalyHandling->processExistingAnomalies();
            $this->crewLimitations->work();

            $startTime = microtime(true);
            $this->handleEscapePods();
            if ($this->loggerUtil->doLog()) {
                $endTime = microtime(true);
                $this->loggerUtil->log(sprintf("\t\thandleEscapePods, seconds: %F", $endTime - $startTime));
            }
            $this->repairActions->work();

            $startTime = microtime(true);
            $entityCount = 0;
            foreach ($this->shipRepository->getPlayerShipsForTick() as $ship) {
                //echo "Processing Ship ".$ship->getId()." at ".microtime()."\n";

                $this->shipTick->work($this->shipWrapperFactory->wrapShip($ship));
                $entityCount++;
            }
            if ($this->loggerUtil->doLog()) {
                $endTime = microtime(true);
                $this->loggerUtil->log(sprintf("\t\tshipTick, seconds: %F", $endTime - $startTime));
            }

            $startTime = microtime(true);
            $this->handleNPCShips();
            if ($this->loggerUtil->doLog()) {
                $endTime = microtime(true);
                $this->loggerUtil->log(sprintf("\t\thandleNPCShips, seconds: %F", $endTime - $startTime));
            }

            $startTime = microtime(true);
            $this->lowerTrumfieldHull();
            $this->lowerOrphanizedTradepostHull();
            $this->lowerStationConstructionHull();
            if ($this->loggerUtil->doLog()) {
                $endTime = microtime(true);
                $this->loggerUtil->log(sprintf("\t\tloweringTrumfieldConstruction, seconds: %F", $endTime - $startTime));
            }

            $this->loggerUtil->init('SHIPTICK', LoggerEnum::LEVEL_WARNING);
            $this->logBenchmarkResult($entityCount);

            $this->anomalyHandling->createNewAnomalies();
        } finally {
            $this->clearLock(1);
        }
    }

    private function handleEscapePods(): void
    {
        $escapedToColonies = [];

        foreach ($this->shipRepository->getEscapePods() as $escapePod) {
            if ($escapePod->getCrewCount() === 0) {
                $this->shipRemover->remove($escapePod);
            }

            if ($escapePod->getStarsystemMap() !== null) {
                $colony = $escapePod->getStarsystemMap()->getColony();

                if ($colony !== null) {
                    $count = $this->transferOwnCrewToColony($escapePod, $colony);

                    if ($count > 0) {
                        if (array_key_exists($colony->getId(), $escapedToColonies)) {
                            $oldCount = $escapedToColonies[$colony->getId()][1];

                            $escapedToColonies[$colony->getId()][1] = $oldCount +  $count;
                        } else {
                            $escapedToColonies[$colony->getId()] = [$colony, $count];
                        }
                    }
                }
            }
        }

        foreach ($escapedToColonies as [$colony, $count]) {
            $msg = sprintf(_('%d deiner Crewman sind aus Fluchtkapseln auf deiner Kolonie %s gelandet'), $count, $colony->getName());
            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $colony->getUser()->getId(),
                $msg,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
            );
        }
    }

    private function transferOwnCrewToColony(ShipInterface $escapePod, ColonyInterface $colony): int
    {
        $count = 0;

        foreach ($escapePod->getCrewAssignments() as $crewAssignment) {
            if ($crewAssignment->getUser() !== $colony->getUser()) {
                continue;
            }

            $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
                $colony
            )->getFreeAssignmentCount();

            if ($freeAssignmentCount === 0) {
                break;
            }

            $count++;
            $crewAssignment->setShip(null);
            $crewAssignment->setSlot(null);
            $crewAssignment->setColony($colony);
            $escapePod->getCrewAssignments()->removeElement($crewAssignment);
            $colony->getCrewAssignments()->add($crewAssignment);
            $this->shipCrewRepository->save($crewAssignment);
        }

        return $count;
    }

    private function lowerTrumfieldHull(): void
    {
        foreach ($this->shipRepository->getDebrisFields() as $ship) {
            $lower = random_int(5, 15);
            if ($ship->getHull() <= $lower) {
                $this->shipRemover->remove($ship);
                continue;
            }
            $ship->setHuell($ship->getHull() - $lower);

            $this->shipRepository->save($ship);
        }
    }

    private function lowerOrphanizedTradepostHull(): void
    {
        foreach ($this->tradePostRepository->getByUser(UserEnum::USER_NOONE) as $tradepost) {
            $ship = $tradepost->getShip();

            $lower = (int)ceil($ship->getMaxHull() / 100);

            if ($ship->getHull() <= $lower) {
                $this->shipRemover->destroy($this->shipWrapperFactory->wrapShip($ship));

                $this->entryCreator->addStationEntry(
                    'Der verlassene Handelsposten in Sektor ' . $ship->getSectorString() . ' ist zerfallen',
                    $ship->getUser()->getId()
                );
                continue;
            }
            $ship->setHuell($ship->getHull() - $lower);

            $this->shipRepository->save($ship);
        }
    }

    private function lowerStationConstructionHull(): void
    {
        foreach ($this->shipRepository->getStationConstructions() as $ship) {
            $lower = random_int(5, 15);
            if ($ship->getHull() <= $lower) {
                $msg = sprintf(_('Dein Konstrukt bei %s war zu lange ungenutzt und ist daher zerfallen'), $ship->getSectorString());
                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $ship->getUser()->getId(),
                    $msg,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION
                );

                $this->shipRemover->remove($ship);
                continue;
            }
            $ship->setHuell($ship->getHull() - $lower);

            $this->shipRepository->save($ship);
        }
    }

    private function handleNPCShips(): void
    {
        // @todo
        foreach ($this->shipRepository->getNpcShipsForTick() as $ship) {
            $wrapper = $this->shipWrapperFactory->wrapShip($ship);
            $reactor = $wrapper->getReactorWrapper();
            if ($reactor === null) {
                continue;
            }

            $epsSystem = $wrapper->getEpsSystemData();
            $warpdrive = $wrapper->getWarpDriveSystemData();

            //load EPS
            if ($epsSystem !== null) {
                $epsSystem->setEps($epsSystem->getEps() + $reactor->getEffectiveEpsProduction())->update();
            }

            //load warpdrive
            if ($warpdrive !== null) {
                $warpdrive->setWarpDrive($warpdrive->getWarpDrive() + $reactor->getEffectiveWarpDriveProduction())->update();
            }
        }
    }

    private function setLock(int $batchGroupId): void
    {
        $this->lockManager->setLock($batchGroupId, LockTypeEnum::SHIP_GROUP);
    }

    private function clearLock(int $batchGroupId): void
    {
        $this->lockManager->clearLock($batchGroupId, LockTypeEnum::SHIP_GROUP);
    }

    protected function getBenchmark(): Ubench
    {
        return $this->benchmark;
    }

    protected function getLoggerUtil(): LoggerUtilInterface
    {
        return $this->loggerUtil;
    }
}
