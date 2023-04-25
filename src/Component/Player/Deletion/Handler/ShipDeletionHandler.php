<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipDeletionHandler implements PlayerDeletionHandlerInterface
{
    private ShipRemoverInterface $shipRemover;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipRemoverInterface $shipRemover,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->shipRemover = $shipRemover;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->entityManager = $entityManager;
    }

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
        $docked = false;

        foreach ($ship->getDockedShips() as $dockedShip) {
            $docked = true;
            $dockedShip->setDockedTo(null);
            $dockedShip->setDockedToId(null);
            $this->shipRepository->save($dockedShip);
        }

        $ship->getDockedShips()->clear();

        if ($docked) {
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
