<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Closure;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;

final class BorgAssimilationDeletionListener
{
    /** @param Closure(): BorgAssimilationService $service */
    public function __construct(private Closure $service) {}

    public function preRemove(PreRemoveEventArgs $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof Spacecraft) {
            ($this->service)()->deleteForSpacecraft($entity);
        } elseif ($entity instanceof SpacecraftRump) {
            ($this->service)()->deleteByRump($entity);
        }
    }
}
