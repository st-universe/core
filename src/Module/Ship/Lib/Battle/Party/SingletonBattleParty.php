<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class SingletonBattleParty extends AbstractBattleParty
{
    private bool $isBase;

    public function __construct(
        ShipWrapperInterface $leader
    ) {
        parent::__construct($leader);

        $this->isBase = $leader->get()->isBase();
    }

    public function initMembers(): Collection
    {
        return $this->createSingleton($this->leader);
    }

    public function isBase(): bool
    {
        return $this->isBase;
    }
}
