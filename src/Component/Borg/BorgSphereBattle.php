<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCoreInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;

final class BorgSphereBattle
{
    public function __construct(
        private SpacecraftAttackCoreInterface $attacks,
        private SpacecraftWrapperFactoryInterface $wrappers,
        private PlayerRelationDeterminatorInterface $relations,
        private FightLibInterface $fight,
        private BorgSphereAttackContext $context
    ) {}

    public function attackTargets(BorgSphereEvent $event, Ship $ship): bool
    {
        if (!$this->canAttack($event, $ship)) {
            return false;
        }
        $source = $this->wrappers->wrapShip($ship);
        $attackedFleets = [];
        $attacked = false;
        foreach ($ship->getLocation()->getSpacecraftsWithoutVacation()->toArray() as $target) {
            if (!$this->canAttack($event, $ship) || !$source->canFire()) {
                break;
            }
            if (!$target instanceof Ship || $this->wasFleetAttacked($target, $attackedFleets) || !$this->isTarget($ship, $target)) {
                continue;
            }
            $isFleetFight = false;
            $targetFleetId = $target->getFleetId();
            $this->performAttack($source, $target, $isFleetFight);
            $attacked = true;
            if ($isFleetFight && $targetFleetId !== null) {
                $attackedFleets[$targetFleetId] = true;
            }
        }
        return $attacked;
    }

    public function attackTarget(BorgSphereEvent $event, Ship $ship, Ship $target): bool
    {
        if (!$this->canAttack($event, $ship) || !$this->isTarget($ship, $target)) {
            return false;
        }
        $source = $this->wrappers->wrapShip($ship);
        if (!$source->canFire()) {
            return false;
        }
        $isFleetFight = false;
        $this->performAttack($source, $target, $isFleetFight);
        return true;
    }

    private function performAttack(SpacecraftWrapperInterface $source, Ship $target, bool &$isFleetFight): void
    {
        $this->context->beginAutomaticAttack($source->get());
        try {
            $this->attacks->attack($source, $this->wrappers->wrapShip($target), false, $isFleetFight, new InformationWrapper());
        } finally {
            $this->context->clear();
        }
    }

    /** @param array<int, true> $attackedFleets */
    private function wasFleetAttacked(Ship $target, array $attackedFleets): bool
    {
        $fleetId = $target->getFleetId();
        return $fleetId !== null && isset($attackedFleets[$fleetId]);
    }

    private function canAttack(BorgSphereEvent $event, Ship $ship): bool
    {
        return !$event->isFinished() && $event->getPhase() !== BorgSpherePhaseEnum::REGENERATING
            && !$ship->getCondition()->isDisabled() && !$ship->getCondition()->isDestroyed()
            && !$ship->isWarped() && !$ship->isCloaked() && $ship->hasEnoughCrew();
    }

    private function isTarget(Ship $source, Ship $target): bool
    {
        return $target->getLocation() === $source->getLocation()
            && $target->getUser()->getId() !== $source->getUser()->getId()
            && $this->isAvailableTarget($target)
            && !$this->relations->isFriend($source->getUser(), $target->getUser())
            && $this->fight->canAttackTarget($source, $target, true);
    }

    private function isAvailableTarget(Ship $target): bool
    {
        return !$target->getCondition()->isDestroyed() && !$target->getCondition()->isDisabled()
            && !$target->isCloaked() && !$target->isWarped() && !$target->getRump()->isEscapePods()
            && !$target->isRpgModuleInvisible() && !$target->getUser()->isVacationRequestOldEnough();
    }
}
