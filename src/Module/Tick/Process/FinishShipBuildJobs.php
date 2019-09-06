<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use ColonyData;
use ColonyShipQueue;
use ColonyShipQueueData;
use PM;
use Stu\Module\Ship\Lib\ShipCreatorInterface;

final class FinishShipBuildJobs implements ProcessTickInterface
{
    private $shipCreator;

    public function __construct(
        ShipCreatorInterface $shipCreator
    ) {
        $this->shipCreator = $shipCreator;
    }

    public function work(): void
    {
        $queue = ColonyShipQueue::getFinishedJobs();
        foreach ($queue as $key => $obj) {
            /**
             * @var ColonyShipQueueData $obj
             */
            /**
             * @var ColonyData $colony
             */
            $colony = ResourceCache()->getObject('colony', $obj->getColonyId());

            $ship = $this->shipCreator->createBy(
                (int) $obj->getUserId(),
                (int) $obj->getRumpId(),
                (int) $obj->getBuildplanId(),
                $colony
            );

            $obj->deleteFromDatabase();
            $txt = _("Auf der Kolonie " . $colony->getNameWithoutMarkup() . " wurde ein Schiff der " . $ship->getRump()->getName() . "-Klasse fertiggestellt");
            PM::sendPM(USER_NOONE, $colony->getUserId(), $txt, PM_SPECIAL_COLONY);
        }
    }
}