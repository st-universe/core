<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;

final class ShipUndocking implements ShipUndockingInterface
{
    public function __construct(
        private CancelRepairInterface $cancelRepair,
        private CancelRetrofitInterface $cancelRetrofit,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}


    #[\Override]
    public function undockShip(Station $station, Ship $dockedShip): void
    {
        $this->cancelRepair->cancelRepair($dockedShip);
        $this->cancelRetrofit->cancelRetrofit($dockedShip);
        $dockedShip->setDockedTo(null);

        $this->privateMessageSender->send(
            $station->getUser()->getId(),
            $dockedShip->getUser()->getId(),
            sprintf(
                'Die %s wurde von der %s abgedockt',
                $dockedShip->getName(),
                $station->getName()
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $dockedShip
        );
    }

    #[\Override]
    public function undockAllDocked(Station $station): bool
    {
        $dockedShips = $station->getDockedShips();
        if ($dockedShips->isEmpty()) {
            return false;
        }

        foreach ($dockedShips as $dockedShip) {
            $this->undockShip($station, $dockedShip);
        }

        $dockedShips->clear();

        return true;
    }
}
