<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;

final class BorgSphereBehavior
{
    public function __construct(private BorgSphereNavigation $navigation, private BorgSphereBattle $battle, private StuRandom $random) {}

    public function work(BorgSphereEvent $event, Ship $ship): void
    {
        if (!$this->canAct($event, $ship)) {
            return;
        }
        if ($this->battle->attackTargets($event, $ship)) {
            return;
        }
        $configuration = $event->getConfiguration();
        if ($this->random->rand(1, 100) <= $configuration->getSetting('idle_chance_percent')) {
            return;
        }
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

    private function canAct(BorgSphereEvent $event, Ship $ship): bool
    {
        return !$event->isFinished() && $event->getPhase() !== BorgSpherePhaseEnum::REGENERATING
            && !$ship->getCondition()->isDisabled() && !$ship->getCondition()->isDestroyed();
    }
}
