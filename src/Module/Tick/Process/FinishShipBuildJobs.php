<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;

final class FinishShipBuildJobs implements ProcessTickInterface
{
    private $shipCreator;

    private $colonyShipQueueRepository;

    private $privateMessageSender;

    public function __construct(
        ShipCreatorInterface $shipCreator,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipCreator = $shipCreator;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function work(): void
    {
        $queue = $this->colonyShipQueueRepository->getFinishedJobs();
        foreach ($queue as $obj) {
            $colony = $obj->getColony();

            $ship = $this->shipCreator->createBy(
                (int) $obj->getUserId(),
                (int) $obj->getRumpId(),
                $obj->getShipBuildplan()->getId(),
                $colony
            );

            $this->colonyShipQueueRepository->delete($obj);

            $txt = _("Auf der Kolonie " . $colony->getName() . " wurde ein Schiff der " . $ship->getRump()->getName() . "-Klasse fertiggestellt");

            $this->privateMessageSender->send(USER_NOONE, (int)$colony->getUserId(), $txt,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY);
        }
    }
}