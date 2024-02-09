<?php

namespace Stu\Module\Tick\Ship\ManagerComponent;

use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

class LowerHull implements ManagerComponentInterface
{
    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRemoverInterface $shipRemover;

    private ShipRepositoryInterface $shipRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private EntryCreatorInterface $entryCreator;

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRemoverInterface $shipRemover,
        ShipRepositoryInterface $shipRepository,
        TradePostRepositoryInterface $tradePostRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        EntryCreatorInterface $entryCreator
    ) {
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRemover = $shipRemover;
        $this->shipRepository = $shipRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->entryCreator = $entryCreator;
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
                $this->shipRemover->destroy($this->shipWrapperFactory->wrapShip($ship));

                $this->entryCreator->addStationEntry(
                    'Der verlassene Handelsposten in Sektor ' . $ship->getSectorString() . ' ist zerfallen',
                    UserEnum::USER_NOONE,
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
}