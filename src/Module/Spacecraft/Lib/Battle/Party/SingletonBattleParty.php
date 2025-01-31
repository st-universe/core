<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class SingletonBattleParty extends AbstractBattleParty
{
    private bool $isStation;

    public function __construct(
        SpacecraftWrapperInterface $leader
    ) {
        parent::__construct($leader);

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
