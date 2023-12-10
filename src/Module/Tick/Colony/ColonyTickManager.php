<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Tick\AbstractTickManager;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Ubench;

final class ColonyTickManager extends AbstractTickManager implements ColonyTickManagerInterface
{
    private ColonyTickInterface $colonyTick;

    private CrewCreatorInterface $crewCreator;

    private CrewTrainingRepositoryInterface $crewTrainingRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private CrewCountRetrieverInterface $crewCountRetriever;

    private LockManagerInterface $lockManager;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    private CrewLimitCalculatorInterface $crewLimitCalculator;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private LoggerUtilInterface $loggerUtil;

    private Ubench $benchmark;

    public function __construct(
        ColonyTickInterface $colonyTick,
        CrewCreatorInterface $crewCreator,
        CrewTrainingRepositoryInterface $crewTrainingRepository,
        ColonyRepositoryInterface $colonyRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        CrewCountRetrieverInterface $crewCountRetriever,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        CrewLimitCalculatorInterface $crewLimitCalculator,
        ColonyLibFactoryInterface $colonyLibFactory,
        LockManagerInterface $lockManager,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        Ubench $benchmark
    ) {
        $this->colonyTick = $colonyTick;
        $this->crewCreator = $crewCreator;
        $this->crewTrainingRepository = $crewTrainingRepository;
        $this->colonyRepository = $colonyRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->crewCountRetriever = $crewCountRetriever;
        $this->lockManager = $lockManager;
        $this->colonyFunctionManager = $colonyFunctionManager;
        $this->crewLimitCalculator = $crewLimitCalculator;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->benchmark = $benchmark;
    }

    public function work(int $batchGroup, int $batchGroupCount): void
    {
        $this->setLock($batchGroup);
        try {
            $entityCount = $this->colonyLoop($batchGroup, $batchGroupCount);
            $this->proceedCrewTraining($batchGroup, $batchGroupCount);

            $this->loggerUtil->init(sprintf(
                'COLOTICK_%dof%d',
                $batchGroup,
                $batchGroupCount
            ), LoggerEnum::LEVEL_WARNING);
            $this->logBenchmarkResult($entityCount);
        } finally {
            $this->clearLock($batchGroup);
        }
    }

    private function colonyLoop(int $batchGroup, int $batchGroupCount): int
    {
        $colonyList = $this->colonyRepository->getByBatchGroup($batchGroup, $batchGroupCount);

        $entityCount = 0;
        foreach ($colonyList as $colony) {
            //echo "Processing Colony ".$colony->getId()." at ".microtime()."\n";

            //handle colony only if vacation mode not active
            if (!$colony->getUser()->isVacationRequestOldEnough()) {
                $this->colonyTick->work($colony);
            }

            $entityCount++;
        }

        return $entityCount;
    }

    private function proceedCrewTraining(int $batchGroup, int $batchGroupCount): void
    {
        $user = [];

        foreach ($this->crewTrainingRepository->getByBatchGroup($batchGroup, $batchGroupCount) as $obj) {
            if (!isset($user[$obj->getUserId()])) {
                $user[$obj->getUserId()] = 0;
            }
            if ($user[$obj->getUserId()] >= $this->crewCountRetriever->getTrainableCount($obj->getUser())) {
                continue;
            }
            $colony = $obj->getColony();

            $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
                $colony
            )->getFreeAssignmentCount();

            //colony can't hold more crew
            if ($freeAssignmentCount === 0) {
                $this->crewTrainingRepository->delete($obj);
                continue;
            }

            $globalCrewLimit = $this->crewLimitCalculator->getGlobalCrewLimit($obj->getUser());

            //user has too much crew
            if ($globalCrewLimit - $this->crewCountRetriever->getAssignedCount($obj->getUser()) <= 0) {
                $this->crewTrainingRepository->delete($obj);
                continue;
            }

            //no academy online
            if (!$this->colonyFunctionManager->hasActiveFunction($colony, BuildingEnum::BUILDING_FUNCTION_ACADEMY)) {
                continue;
            }
            $this->crewCreator->create($obj->getUserId(), $colony);

            $this->crewTrainingRepository->delete($obj);
            $user[$obj->getUserId()]++;
        }

        // send message for crew training
        foreach ($user as $userId => $count) {
            if ($count === 0) {
                continue;
            }

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $userId,
                sprintf(
                    "Es wurden erfolgreich %d Crewman ausgebildet.",
                    $count
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
            );
        }
    }

    private function setLock(int $batchGroupId): void
    {
        $this->lockManager->setLock($batchGroupId, LockTypeEnum::COLONY_GROUP);
    }

    private function clearLock(int $batchGroupId): void
    {
        $this->lockManager->clearLock($batchGroupId, LockTypeEnum::COLONY_GROUP);
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
