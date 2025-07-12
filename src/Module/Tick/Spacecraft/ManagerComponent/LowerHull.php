<?php

namespace Stu\Module\Tick\Spacecraft\ManagerComponent;

use Override;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\TrumfieldRepositoryInterface;

class LowerHull implements ManagerComponentInterface
{
    public function __construct(
        private PrivateMessageSenderInterface $privateMessageSender,
        private SpacecraftRemoverInterface $spacecraftRemover,
        private TrumfieldRepositoryInterface $trumfieldRepository,
        private SpacecraftDestructionInterface $spacecraftDestruction,
        private StationRepositoryInterface $stationRepository,
        private TradePostRepositoryInterface $tradePostRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[Override]
    public function work(): void
    {
        $this->lowerTrumfieldHull();
        $this->lowerOrphanizedTradepostHull();
        $this->lowerStationConstructionHull();
    }

    private function lowerTrumfieldHull(): void
    {
        foreach ($this->trumfieldRepository->findAll() as $trumfield) {
            $lower = random_int(5, 15);
            if ($trumfield->getHull() <= $lower) {
                $this->trumfieldRepository->delete($trumfield);
                continue;
            }
            $trumfield->setHull($trumfield->getHull() - $lower);

            $this->trumfieldRepository->save($trumfield);
        }
    }

    private function lowerOrphanizedTradepostHull(): void
    {
        foreach ($this->tradePostRepository->getByUser(UserConstants::USER_NOONE) as $tradepost) {
            $station = $tradepost->getStation();
            $condition = $station->getCondition();

            $lower = (int)ceil($station->getMaxHull() / 100);

            if ($condition->getHull() <= $lower) {
                $this->spacecraftDestruction->destroy(
                    null,
                    $this->spacecraftWrapperFactory->wrapStation($station),
                    SpacecraftDestructionCauseEnum::ORPHANIZED_TRADEPOST,
                    new InformationWrapper()
                );

                continue;
            }
            $condition->changeHull(-$lower);

            $this->stationRepository->save($station);
        }
    }

    private function lowerStationConstructionHull(): void
    {
        foreach ($this->stationRepository->getStationConstructions() as $station) {

            $condition = $station->getCondition();
            $lower = random_int(5, 15);

            if ($condition->getHull() <= $lower) {
                $msg = sprintf(_('Dein Konstrukt bei %s war zu lange ungenutzt und ist daher zerfallen'), $station->getSectorString());
                $this->privateMessageSender->send(
                    UserConstants::USER_NOONE,
                    $station->getUser()->getId(),
                    $msg,
                    PrivateMessageFolderTypeEnum::SPECIAL_STATION
                );

                $this->spacecraftRemover->remove($station);
                continue;
            }
            $condition->changeHull(-$lower);

            $this->stationRepository->save($station);
        }
    }
}
