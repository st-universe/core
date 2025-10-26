<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class MixedBattleParty extends AbstractBattleParty
{
    /** @param Collection<int, covariant SpacecraftWrapperInterface> $wrappers */
    public function __construct(
        private Collection $wrappers,
        StuRandom $stuRandom
    ) {
        $first = $wrappers->first();
        if (!$first) {
            throw new RuntimeException('empty collection is not allowed');
        }
        parent::__construct($first, $stuRandom);
    }

    #[\Override]
    public function initMembers(): Collection
    {
        return $this->wrappers;
    }
}
