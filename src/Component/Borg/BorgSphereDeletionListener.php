<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Closure;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\SpacecraftBuildplan;

final class BorgSphereDeletionListener
{
    /** @param Closure(): BorgSphereDeletionHandler $handler */
    public function __construct(private Closure $handler) {}

    public function preRemove(PreRemoveEventArgs $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof Colony) {
            ($this->handler)()->deleteByColony($entity);
        } elseif ($entity instanceof NPCQuest) {
            ($this->handler)()->deleteByQuest($entity);
        } elseif ($entity instanceof SpacecraftBuildplan) {
            ($this->handler)()->deleteByBuildplan($entity);
        }
    }
}
