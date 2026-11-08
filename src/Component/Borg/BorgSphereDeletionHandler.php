<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Override;
use Stu\Component\Player\Deletion\Handler\PlayerDeletionHandlerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class BorgSphereDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private BorgSphereRepositoryInterface $events, private ShipRepositoryInterface $ships, private SpacecraftRemoverInterface $remover) {}

    #[Override]
    public function delete(User $user): void
    {
        $this->events->deleteRaids($user);
        $this->removeMatching(static fn (BorgSphereConfiguration $configuration): bool => $configuration->getUser() === $user || $configuration->getQuest()->getUser() === $user || $configuration->getBuildplan()->getUser() === $user || $configuration->getColony()->getUser() === $user);
    }

    public function deleteByColony(Colony $colony): void
    {
        $this->removeMatching(static fn (BorgSphereConfiguration $configuration): bool => $configuration->getColony() === $colony);
    }

    public function deleteByQuest(NPCQuest $quest): void
    {
        $this->removeMatching(static fn (BorgSphereConfiguration $configuration): bool => $configuration->getQuest() === $quest);
    }

    public function deleteByBuildplan(SpacecraftBuildplan $buildplan): void
    {
        $this->removeMatching(static fn (BorgSphereConfiguration $configuration): bool => $configuration->getBuildplan() === $buildplan);
    }

    /** @param callable(BorgSphereConfiguration): bool $matches */
    private function removeMatching(callable $matches): void
    {
        foreach ($this->events->getConfigurations() as $configuration) {
            if (!$matches($configuration)) {
                continue;
            }
            $event = $this->events->getByConfiguration($configuration);
            $ship = $event?->getShipId() === null ? null : $this->ships->find($event->getShipId());
            if ($ship !== null) {
                $this->remover->remove($ship, true);
            }
            $this->events->deleteConfiguration($configuration);
        }
    }
}
