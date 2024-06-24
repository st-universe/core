<?php

namespace Stu\Module\Tick\Ship\ManagerComponent;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

class LowerHull implements ManagerComponentInterface
{
    public function __construct(
        private PrivateMessageSenderInterface $privateMessageSender,
        private ShipRemoverInterface $shipRemover,
        private ShipDestructionInterface $shipDestruction,
        private ShipRepositoryInterface $shipRepository,
        private TradePostRepositoryInterface $tradePostRepository,
        private ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
    }

    public function work(): void
    {
        $this->lowerTrumfieldHull();
        $this->lowerOrphanizedTradepostHull();
        $this->lowerStationConstructionHull();
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
                $this->shipDestruction->destroy(
                    null,
                    $this->shipWrapperFactory->wrapShip($ship),
                    ShipDestructionCauseEnum::ORPHANIZED_TRADEPOST,
                    new InformationWrapper()
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
                    PrivateMessageFolderTypeEnum::SPECIAL_STATION
                );

                $this->shipRemover->remove($ship);
                continue;
            }
            $ship->setHuell($ship->getHull() - $lower);

            $this->shipRepository->save($ship);
        }
    }
}
