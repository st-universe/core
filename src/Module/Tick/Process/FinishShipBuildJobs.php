<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;

final class FinishShipBuildJobs implements ProcessTickHandlerInterface
{
    public function __construct(private ShipCreatorInterface $shipCreator, private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository, private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository, private PrivateMessageSenderInterface $privateMessageSender) {}

    #[Override]
    public function work(): void
    {
        $queue = $this->colonyShipQueueRepository->getFinishedJobs();
        foreach ($queue as $obj) {
            if ($obj->getMode() == 1) {

                $colony = $obj->getColony();

                $ship = $this->shipCreator->createBy(
                    $obj->getUserId(),
                    $obj->getRumpId(),
                    $obj->getSpacecraftBuildplan()->getId()
                )
                    ->setLocation($colony->getStarsystemMap())
                    ->finishConfiguration()
                    ->get();

                $this->colonyShipQueueRepository->delete($obj);

                $txt = _("Auf der Kolonie " . $colony->getName() . " wurde ein Schiff der " . $ship->getRump()->getName() . "-Klasse fertiggestellt");

                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $colony->getUserId(),
                    $txt,
                    PrivateMessageFolderTypeEnum::SPECIAL_COLONY
                );
            }
        }

        $queue = $this->shipyardShipQueueRepository->getFinishedJobs();
        foreach ($queue as $obj) {
            $shipyard = $obj->getStation();

            $ship = $this->shipCreator->createBy(
                $obj->getUserId(),
                $obj->getRumpId(),
                $obj->getSpacecraftBuildplan()->getId()
            )
                ->setLocation($shipyard->getLocation())
                ->finishConfiguration()
                ->get();

            $this->shipyardShipQueueRepository->delete($obj);

            $txt = _("Auf der Werftstation " . $shipyard->getName() . " wurde ein Schiff der " . $ship->getRump()->getName() . "-Klasse fertiggestellt");

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $shipyard->getUser()->getId(),
                $txt,
                PrivateMessageFolderTypeEnum::SPECIAL_STATION
            );
        }
    }
}
