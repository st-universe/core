<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Override;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipUndocking implements ShipUndockingInterface
{
    public function __construct(private ShipRepositoryInterface $shipRepository, private PrivateMessageSenderInterface $privateMessageSender)
    {
    }

    #[Override]
    public function undockAllDocked(ShipInterface $station): bool
    {
        $dockedShips = $station->getDockedShips();
        if ($dockedShips->isEmpty()) {
            return false;
        }

        foreach ($dockedShips as $dockedShip) {
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
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $href
            );
        }

        $dockedShips->clear();

        return true;
    }
}
