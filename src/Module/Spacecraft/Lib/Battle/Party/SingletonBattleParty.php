<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class SingletonBattleParty extends AbstractBattleParty
{
    private bool $isStation;

    public function __construct(
        SpacecraftWrapperInterface $leader,
        StuRandom $stuRandom
    ) {
        parent::__construct($leader, $stuRandom);

        $this->isStation = $leader->get()->isStation();
    }

    #[Override]
    public function initMembers(): Collection
    {
        return $this->createSingleton($this->leader);
    }

    #[Override]
    public function isStation(): bool
    {
        return $this->isStation;
    }
}
