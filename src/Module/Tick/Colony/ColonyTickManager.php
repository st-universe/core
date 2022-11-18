<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Stu\Component\Building\BuildingEnum;
use Stu\Component\Game\GameEnum;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;

final class ColonyTickManager implements ColonyTickManagerInterface
{
    public const LOCKFILE_DIR = '/var/tmp/';

    private ColonyTickInterface $colonyTick;

    private CrewCreatorInterface $crewCreator;

    private CrewTrainingRepositoryInterface $crewTrainingRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private CommodityRepositoryInterface $commodityRepository;

    public function __construct(
        ColonyTickInterface $colonyTick,
        CrewCreatorInterface $crewCreator,
        CrewTrainingRepositoryInterface $crewTrainingRepository,
        ColonyRepositoryInterface $colonyRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->colonyTick = $colonyTick;
        $this->crewCreator = $crewCreator;
        $this->crewTrainingRepository = $crewTrainingRepository;
        $this->colonyRepository = $colonyRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->commodityRepository = $commodityRepository;
    }

    public function work(int $tickId): void
    {
        $this->setLock($tickId);
        $this->colonyLoop($tickId);
        $this->proceedCrewTraining($tickId);
        $this->clearLock($tickId);
    }

    private function colonyLoop(int $tickId): void
    {
        $commodityArray = $this->commodityRepository->getAll();
        $colonyList = $this->colonyRepository->getByTick($tickId);

        foreach ($colonyList as $colony) {
            //echo "Processing Colony ".$colony->getId()." at ".microtime()."\n";

            //handle colony only if vacation mode not active
            if (!$colony->getUser()->isVacationRequestOldEnough()) {
                $this->colonyTick->work($colony, $commodityArray);
            }
        }
    }

    private function proceedCrewTraining(int $tickId): void
    {
        $user = array();
        foreach ($this->crewTrainingRepository->getByTick($tickId) as $obj) {
            if (!isset($user[$obj->getUserId()])) {
                $user[$obj->getUserId()] = 0;
            }
            if ($user[$obj->getUserId()] >= $obj->getUser()->getTrainableCrewCountMax()) {
                continue;
            }
            if ($obj->getUser()->getGlobalCrewLimit() - $obj->getUser()->getAssignedCrewCount() <= 0) {
                $this->crewTrainingRepository->delete($obj);
                continue;
            }
            if (!$obj->getColony()->hasActiveBuildingWithFunction(BuildingEnum::BUILDING_FUNCTION_ACADEMY)) {
                continue;
            }
            $this->crewCreator->create($obj->getUserId(), $obj->getColony());

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

    private function setLock(int $tickId): void
    {
        @touch(self::LOCKFILE_DIR . $tickId . '.lock');
    }

    private function clearLock(int $tickId): void
    {
        @unlink(self::LOCKFILE_DIR . $tickId . '.lock');
    }
}
