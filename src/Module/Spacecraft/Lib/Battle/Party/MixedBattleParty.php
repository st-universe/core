<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class MixedBattleParty extends AbstractBattleParty
{
    /** @param Collection<int, covariant SpacecraftWrapperInterface> $wrappers */
    public function __construct(
        private Collection $wrappers
    ) {
        parent::__construct($wrappers->first());
    }

    #[Override]
    public function initMembers(): Collection
    {
        return $this->wrappers;
    }
}
