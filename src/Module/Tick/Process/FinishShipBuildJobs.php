<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;

final class FinishShipBuildJobs implements ProcessTickHandlerInterface
{
    private ShipCreatorInterface $shipCreator;

    private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipCreatorInterface $shipCreator,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipCreator = $shipCreator;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->shipyardShipQueueRepository = $shipyardShipQueueRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function work(): void
    {
        $queue = $this->colonyShipQueueRepository->getFinishedJobs();
        foreach ($queue as $obj) {
            $colony = $obj->getColony();

            $ship = $this->shipCreator->createBy(
                $obj->getUserId(),
                $obj->getRumpId(),
                $obj->getShipBuildplan()->getId(),
                $colony
            )
                ->finishConfiguration()
                ->get();

            $this->colonyShipQueueRepository->delete($obj);

            $txt = _("Auf der Kolonie " . $colony->getName() . " wurde ein Schiff der " . $ship->getRump()->getName() . "-Klasse fertiggestellt");

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $colony->getUserId(),
                $txt,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
            );
        }

        $queue = $this->shipyardShipQueueRepository->getFinishedJobs();
        foreach ($queue as $obj) {
            $shipyard = $obj->getShip();

            $ship = $this->shipCreator->createBy(
                $obj->getUserId(),
                $obj->getRumpId(),
                $obj->getShipBuildplan()->getId()
            )
                ->setLocation($shipyard->getLocation()->get())
                ->finishConfiguration()
                ->get();

            $this->shipyardShipQueueRepository->delete($obj);

            $txt = _("Auf der Werftstation " . $shipyard->getName() . " wurde ein Schiff der " . $ship->getRump()->getName() . "-Klasse fertiggestellt");

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $shipyard->getUser()->getId(),
                $txt,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION
            );
        }
    }
}
