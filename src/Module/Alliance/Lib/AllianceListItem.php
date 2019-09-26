<?php

// @todo enable strict typing
declare(strict_types=0);

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\AllianceInterface;

final class AllianceListItem implements AllianceListItemInterface
{
    private $alliance;

    public function __construct(
        AllianceInterface $alliance
    ) {
        $this->alliance = $alliance;
    }

    public function getId(): int
    {
        return $this->alliance->getId();
    }

    public function getName(): string
    {
        return $this->alliance->getName();
    }

    public function getFactionId(): ?int
    {
        return $this->alliance->getFactionId();
    }

    public function getMemberCount(): int
    {
        return count($this->alliance->getMembers());
    }

    public function acceptsApplications(): bool
    {
        return $this->alliance->getAcceptApplications() == 1;
    }
}
