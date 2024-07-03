<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\FactionInterface;

final class AllianceListItem
{
    public function __construct(private AllianceInterface $alliance)
    {
    }

    public function getId(): int
    {
        return $this->alliance->getId();
    }

    public function getName(): string
    {
        return $this->alliance->getName();
    }

    public function getFaction(): ?FactionInterface
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

    public function getMembers(): iterable
    {
        return $this->alliance->getMembers();
    }
}
