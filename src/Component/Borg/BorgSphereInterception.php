<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Interaction\InterceptShipCoreInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;

final class BorgSphereInterception
{
    public function __construct(
        private InterceptShipCoreInterface $interception,
        private SpacecraftWrapperFactoryInterface $wrappers,
        private PlayerRelationDeterminatorInterface $relations,
        private BorgSphereBattle $battle,
        private BorgSphereAttackContext $context,
        private StuRandom $random
    ) {}

    public function interceptAndAttack(BorgSphereEvent $event, Ship $ship): bool
    {
        if (!$this->isAvailable($event, $ship)) {
            return false;
        }
        $source = $this->wrappers->wrapShip($ship);
        if (!$source->canFire()) {
            return false;
        }
        $groups = $this->targets($ship);
        if ($groups === [] || $this->random->rand(1, 100) > $event->getConfiguration()->getSetting('intercept_chance_percent')) {
            return false;
        }
        $target = $groups[$this->random->array_rand($groups)];
        $this->context->beginAutomaticAttack($ship);
        try {
            $this->interception->intercept($source, $this->wrappers->wrapShip($target), new InformationWrapper(), false, true);
            $this->battle->attackTarget($event, $ship, $target);
        } finally {
            $this->context->clear();
        }
        return true;
    }

    private function isAvailable(BorgSphereEvent $event, Ship $ship): bool
    {
        return !$event->isFinished() && $event->getPhase() !== BorgSpherePhaseEnum::REGENERATING
            && !$ship->getCondition()->isDisabled() && !$ship->getCondition()->isDestroyed()
            && !$ship->isWarped() && !$ship->isCloaked() && $ship->canIntercept() && $ship->hasEnoughCrew();
    }

    /** @return array<string, Ship> */
    private function targets(Ship $source): array
    {
        $count = 0;
        $groups = [];
        foreach ($source->getLocation()->getSpacecraftsWithoutVacation() as $target) {
            if (!$target instanceof Ship || !$this->isTarget($source, $target)) {
                continue;
            }
            $count++;
            $key = $target->getFleetId() === null ? 'ship:' . $target->getId() : 'fleet:' . $target->getFleetId();
            $groups[$key] ??= $target;
        }
        return $count < 2 ? [] : $groups;
    }

    private function isTarget(Ship $source, Ship $target): bool
    {
        return $target->getLocation() === $source->getLocation()
            && $target->getUser()->getId() !== $source->getUser()->getId()
            && $target->isWarped() && !$target->isCloaked() && !$target->isRpgModuleInvisible()
            && !$target->getCondition()->isDisabled() && !$target->getCondition()->isDestroyed()
            && !$target->getRump()->isEscapePods()
            && !$this->relations->isFriend($source->getUser(), $target->getUser());
    }
}
