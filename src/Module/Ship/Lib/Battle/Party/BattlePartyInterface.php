<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Countable;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\UserInterface;

interface BattlePartyInterface extends Countable
{
    public function getUser(): UserInterface;

    public function getLeader(): ShipWrapperInterface;

    /** @return Collection<int, ShipWrapperInterface> */
    public function getActiveMembers(bool $canFire = false, bool $filterDisabled = true): Collection;

    public function getRandomActiveMember(): ShipWrapperInterface;

    public function isDefeated(): bool;

    public function isBase(): bool;

    public function getPrivateMessageType(): int;
}
