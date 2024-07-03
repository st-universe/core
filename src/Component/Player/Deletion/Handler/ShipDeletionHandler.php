<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private ShipRemoverInterface $shipRemover, private ShipRepositoryInterface $shipRepository, private ShipSystemManagerInterface $shipSystemManager, private ShipWrapperFactoryInterface $shipWrapperFactory, private ShipUndockingInterface $shipUndocking, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function delete(UserInterface $user): void
    {
        foreach ($this->shipRepository->getByUser($user) as $ship) {
            if ($ship->getTradePost() === null) {
                $this->unsetTractor($ship);
                $this->undockAllDockedShips($ship);

                $this->shipRemover->remove($ship, true);
            }
        }
    }

    private function undockAllDockedShips(ShipInterface $ship): void
    {
        $anyDocked = $this->shipUndocking->undockAllDocked($ship);

        if ($anyDocked) {
            $this->entityManager->flush();
        }
    }

    private function unsetTractor(ShipInterface $ship): void
    {
        $tractoredShip = $ship->getTractoredShip();

        if ($tractoredShip === null) {
            return;
        }

        $this->shipSystemManager->deactivate(
            $this->shipWrapperFactory->wrapShip($ship),
            ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM,
            true
        );
    }
}
