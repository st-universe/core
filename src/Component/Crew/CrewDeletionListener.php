<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Closure;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Stu\Orm\Entity\Crew;

final class CrewDeletionListener
{
    public function __construct(private Closure $handler) {}

    public function preRemove(PreRemoveEventArgs $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof Crew) {
            ($this->handler)()->delete($entity);
        }
    }
}
