<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use RuntimeException;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class MixedBattleParty extends AbstractBattleParty
{
    /** @param Collection<int, covariant SpacecraftWrapperInterface> $wrappers */
    public function __construct(
        private Collection $wrappers
    ) {
        $first = $wrappers->first();
        if (!$first) {
            throw new RuntimeException('empty collection is not allowed');
        }
        parent::__construct($first);
    }

    #[Override]
    public function initMembers(): Collection
    {
        return $this->wrappers;
    }
}
