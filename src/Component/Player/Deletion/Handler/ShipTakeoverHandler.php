<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipTakeoverRepositoryInterface;

final class ShipTakeoverHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private ShipTakeoverRepositoryInterface $shipTakeoverRepository,
        private ShipTakeoverManagerInterface $shipTakeoverManager
    ) {}

    #[Override]
    public function delete(UserInterface $user): void
    {
        foreach ($this->shipTakeoverRepository->getByTargetOwner($user) as $shipTakeover) {

            $this->shipTakeoverManager->cancelTakeover(
                $shipTakeover,
                ', da der Spieler des Ziels gelöscht wurde'
            );
        }
    }
}
