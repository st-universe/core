<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;

final class BorgSphereBehavior
{
    public function __construct(private BorgSphereNavigation $navigation) {}

    public function work(BorgSphereEvent $event, Ship $ship): void
    {
        if ($event->getPhase() === BorgSpherePhaseEnum::REGENERATING || $ship->getCondition()->isDisabled()) {
            return;
        }
        $configuration = $event->getConfiguration();
        if ($event->getPhase() === BorgSpherePhaseEnum::APPROACH) {
            $this->navigation->approach($ship, $configuration);
            if ($ship->getLocation() === $configuration->getColony()->getStarsystemMap()) {
                $event->setPhase(BorgSpherePhaseEnum::ROAMING);
            }
            return;
        }
        if (!$ship->getCondition()->isDestroyed()) {
            $this->navigation->roam($ship, $configuration);
        }
    }
}
