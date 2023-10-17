<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipUndocking implements ShipUndockingInterface
{
    private ShipRepositoryInterface $shipRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipRepository = $shipRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function undockAllDocked(ShipInterface $station): bool
    {
        $anyDocked = $station->getDockedShipCount() > 0;

        foreach ($station->getDockedShips() as $dockedShip) {
            $dockedShip->setDockedTo(null);
            $dockedShip->setDockedToId(null);
            $this->shipRepository->save($dockedShip);

            $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $dockedShip->getId());

            $this->privateMessageSender->send(
                $station->getUser()->getId(),
                $dockedShip->getUser()->getId(),
                sprintf(
                    'Die %s wurde von der %s abgedockt',
                    $dockedShip->getName(),
                    $station->getName()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                $href
            );
        }

        $station->getDockedShips()->clear();

        return $anyDocked;
    }
}
