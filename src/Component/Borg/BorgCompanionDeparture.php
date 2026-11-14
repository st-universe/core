<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Orm\Entity\Ship;

final class BorgCompanionDeparture
{
    public function __construct(private BorgAssimilationLifecycle $assimilation, private SpacecraftRemoverInterface $remover, private EntityManagerInterface $entityManager) {}

    public function depart(Ship $leader): void
    {
        $this->assimilation->releaseForSource($leader, false);
        if ($leader->getFleet() !== null) {
            $this->entityManager->flush();
        }
        foreach ($leader->getFleet()?->getShips()->toArray() ?? [] as $ship) {
            if ($ship !== $leader && $ship->getLocation() === $leader->getLocation()
                && $ship->getUser()->getId() === $leader->getUser()->getId()
                && $ship->getAssimilation()?->isComplete()) {
                $this->remover->remove($ship, true);
            }
        }
    }
}
