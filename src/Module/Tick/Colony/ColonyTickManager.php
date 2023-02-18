<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Tick\Lock\LockEnum;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;

final class ColonyTickManager implements ColonyTickManagerInterface
{
    private ColonyTickInterface $colonyTick;

    private CrewCreatorInterface $crewCreator;

    private CrewTrainingRepositoryInterface $crewTrainingRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private CommodityRepositoryInterface $commodityRepository;

    private CrewCountRetrieverInterface $crewCountRetriever;

    private LockManagerInterface $lockManager;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    private CrewLimitCalculatorInterface $crewLimitCalculator;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyTickInterface $colonyTick,
        CrewCreatorInterface $crewCreator,
        CrewTrainingRepositoryInterface $crewTrainingRepository,
        ColonyRepositoryInterface $colonyRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        CommodityRepositoryInterface $commodityRepository,
        CrewCountRetrieverInterface $crewCountRetriever,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        CrewLimitCalculatorInterface $crewLimitCalculator,
        ColonyLibFactoryInterface $colonyLibFactory,
        LockManagerInterface $lockManager
    ) {
        $this->colonyTick = $colonyTick;
        $this->crewCreator = $crewCreator;
        $this->crewTrainingRepository = $crewTrainingRepository;
        $this->colonyRepository = $colonyRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->commodityRepository = $commodityRepository;
        $this->crewCountRetriever = $crewCountRetriever;
        $this->lockManager = $lockManager;
        $this->colonyFunctionManager = $colonyFunctionManager;
        $this->crewLimitCalculator = $crewLimitCalculator;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function work(int $batchGroup, int $batchGroupCount): void
    {
        $this->setLock($batchGroup);
        try {
            $this->colonyLoop($batchGroup, $batchGroupCount);
            $this->proceedCrewTraining($batchGroup, $batchGroupCount);
        } finally {
            $this->clearLock($batchGroup);
        }
    }

    private function colonyLoop(int $batchGroup, int $batchGroupCount): void
    {
        $commodityArray = $this->commodityRepository->getAll();
        $colonyList = $this->colonyRepository->getByBatchGroup($batchGroup, $batchGroupCount);

        foreach ($colonyList as $colony) {
            //echo "Processing Colony ".$colony->getId()." at ".microtime()."\n";

            //handle colony only if vacation mode not active
            if (!$colony->getUser()->isVacationRequestOldEnough()) {
                $this->colonyTick->work($colony, $commodityArray);
            }
        }
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
                $colony,
                $this->colonyLibFactory->createColonyCommodityProduction($colony)->getProduction()
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
                GameEnum::USER_NOONE,
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
        $this->lockManager->setLock($batchGroupId, LockEnum::LOCK_TYPE_COLONY_GROUP);
    }

    private function clearLock(int $batchGroupId): void
    {
        $this->lockManager->clearLock($batchGroupId, LockEnum::LOCK_TYPE_COLONY_GROUP);
    }
}
