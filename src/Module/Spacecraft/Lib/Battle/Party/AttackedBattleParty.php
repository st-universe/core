<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Component\Borg\BorgSphereAttackContext;
use Stu\Module\Control\StuRandom;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;

class AttackedBattleParty extends AbstractBattleParty
{
    public function __construct(
        SpacecraftWrapperInterface $leader,
        StuRandom $stuRandom,
        private ?BorgSphereAttackContext $borgSphereAttackContext = null
    ) {
        parent::__construct($leader, $stuRandom);
    }

    #[Override]
    public function getActiveMembers(bool $canFire = false, bool $filterDisabled = true): Collection
    {
        $target = $this->leader->get();
        if ($target instanceof Ship && $target->isBeingAssimilated()) {
            return $canFire ? new ArrayCollection() : $this->createSingleton($this->leader)
                ->filter(static fn (SpacecraftWrapperInterface $wrapper): bool => !$wrapper->get()->getCondition()->isDestroyed());
        }
        if (!$canFire && $this->borgSphereAttackContext?->allowsDestruction($target)) {
            return parent::getActiveMembers(false, false)->filter(fn (SpacecraftWrapperInterface $wrapper): bool =>
                !$wrapper->get()->getCondition()->isDisabled() || $wrapper->get() === $target);
        }
        $isOrphanedTradepost = $target instanceof Station
            && $target->getTradePost()?->getUserId() === UserConstants::USER_NOONE;

        return parent::getActiveMembers(
            $canFire,
            $filterDisabled && ($canFire || !$isOrphanedTradepost)
        )->filter(fn (SpacecraftWrapperInterface $wrapper): bool =>
            $this->borgSphereAttackContext?->allowsAutomaticAttackTarget($wrapper->get()) !== false);
    }

    #[Override]
    protected function initMembers(): Collection
    {
        $fleetWrapper = $this->leader->getFleetWrapper();

        if ($fleetWrapper !== null) {

            // only uncloaked defenders fight
            $uncloakedDefenders = $fleetWrapper->getShipWrappers()
                ->filter(fn (SpacecraftWrapperInterface $wrapper): bool => !$wrapper->get()->isCloaked())
                ->toArray();

            $allDefenders = $this->addDockedTo($uncloakedDefenders);

            // if all defenders were cloaked, they obviously were scanned and enter the fight as a whole fleet
            return $allDefenders->isEmpty()
                ? $fleetWrapper->getShipWrappers()
                : $allDefenders;
        }
        return $this->addDockedTo([$this->leader->get()->getId() => $this->leader]);
    }

    /**
     * @param array<int, covariant SpacecraftWrapperInterface> $spacecrafts
     *
     * @return Collection<int, SpacecraftWrapperInterface>
     */
    private function addDockedTo(array $spacecrafts): Collection
    {
        $dockedToWrappers = [];

        foreach ($spacecrafts as $wrapper) {
            $dockedToWrapper = $wrapper instanceof ShipWrapperInterface ? $wrapper->getDockedToStationWrapper() : null;
            if (
                $dockedToWrapper === null
                || $dockedToWrapper->get()->getUser()->isNpc()
            ) {
                continue;
            }

            $dockedToWrappers[$dockedToWrapper->get()->getId()] = $dockedToWrapper;
        }

        return new ArrayCollection($spacecrafts + $dockedToWrappers);
    }
}
