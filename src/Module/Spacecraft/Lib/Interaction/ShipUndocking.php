<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Override;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipUndocking implements ShipUndockingInterface
{
    public function __construct(private ShipRepositoryInterface $shipRepository, private PrivateMessageSenderInterface $privateMessageSender) {}

    #[Override]
    public function undockAllDocked(StationInterface $station): bool
    {
        $dockedShips = $station->getDockedShips();
        if ($dockedShips->isEmpty()) {
            return false;
        }

        foreach ($dockedShips as $dockedShip) {
            $dockedShip->setDockedTo(null);
            $dockedShip->setDockedToId(null);
            $this->shipRepository->save($dockedShip);

            $this->privateMessageSender->send(
                $station->getUser()->getId(),
                $dockedShip->getUser()->getId(),
                sprintf(
                    'Die %s wurde von der %s abgedockt',
                    $dockedShip->getName(),
                    $station->getName()
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $dockedShip->getHref()
            );
        }

        $dockedShips->clear();

        return true;
    }
}
