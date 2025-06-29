<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\User;

final class AllianceListItem
{
    public function __construct(private Alliance $alliance) {}

    public function getId(): int
    {
        return $this->alliance->getId();
    }

    public function getName(): string
    {
        return $this->alliance->getName();
    }

    public function getFaction(): ?Faction
    {
        return $this->alliance->getFaction();
    }

    public function getMemberCount(): int
    {
        return count($this->alliance->getMembers());
    }

    public function acceptsApplications(): bool
    {
        return $this->alliance->getAcceptApplications() == 1;
    }

    public function hasAvatar(): bool
    {
        return $this->alliance->getAvatar() !== '';
    }

    public function getAvatar(): string
    {
        return $this->alliance->getAvatar();
    }

    /** @return Collection<int, User> */
    public function getMembers(): Collection
    {
        return $this->alliance->getMembers();
    }
}
