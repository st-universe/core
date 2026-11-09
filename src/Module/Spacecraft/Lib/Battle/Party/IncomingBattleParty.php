<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Component\Borg\BorgSphereAttackContext;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class IncomingBattleParty extends AbstractBattleParty
{
    public function __construct(
        SpacecraftWrapperInterface $leader,
        StuRandom $stuRandom,
        private ?BorgSphereAttackContext $borgSphereAttackContext = null
    ) {
        parent::__construct($leader, $stuRandom);
    }

    /** @return Collection<int, SpacecraftWrapperInterface> */
    #[Override]
    public function getActiveMembers(bool $canFire = false, bool $filterDisabled = true): Collection
    {
        return parent::getActiveMembers($canFire, $filterDisabled)
            ->filter(fn (SpacecraftWrapperInterface $wrapper): bool =>
                $this->borgSphereAttackContext?->allowsAutomaticAttackTarget($wrapper->get()) !== false);
    }

    #[Override]
    public function initMembers(): Collection
    {
        $fleetWrapper = $this->leader->getFleetWrapper();

        $result = $fleetWrapper === null ? $this->createSingleton($this->leader) : $fleetWrapper->getShipWrappers();

        // filter warped and cloaked
        return $result
            ->filter(fn (SpacecraftWrapperInterface $wrapper): bool =>
            !$wrapper->get()->isCloaked() && !$wrapper->get()->isWarped());
    }
}
