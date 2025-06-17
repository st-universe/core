<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Countable;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\UserInterface;

interface BattlePartyInterface extends Countable
{
    public function getUser(): UserInterface;

    public function getLeader(): SpacecraftWrapperInterface;

    /** @return Collection<int, SpacecraftWrapperInterface> */
    public function getActiveMembers(bool $canFire = false, bool $filterDisabled = true): Collection;

    public function getRandomActiveMember(): SpacecraftWrapperInterface;

    public function isDefeated(): bool;

    public function isStation(): bool;

    public function getPrivateMessageType(): PrivateMessageFolderTypeEnum;
}
