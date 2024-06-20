<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class MixedBattleParty extends AbstractBattleParty
{
    /** @param Collection<int, ShipWrapperInterface> $wrappers */
    public function __construct(
        private Collection $wrappers
    ) {
        parent::__construct($wrappers->first());
    }

    public function initMembers(): Collection
    {
        return $this->wrappers;
    }
}
