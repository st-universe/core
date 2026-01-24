<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class ManagementListItem
{
    public function __construct(
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private Alliance $alliance,
        private User $user,
        private int $currentUserId,
        private CrewLimitCalculatorInterface $crewLimitCalculator,
        private CrewCountRetrieverInterface $crewCountRetriever,
        private AllianceJobManagerInterface $allianceJobManager
    ) {}

    public function getId(): int
    {
        return $this->user->getId();
    }

    public function getFaction(): int
    {
        return $this->user->getFactionId();
    }

    public function getName(): string
    {
        return $this->user->getName();
    }

    public function getLastActionDate(): int
    {
        return $this->user->getLastAction();
    }

    public function getCrewOnShips(): int
    {
        return $this->crewCountRetriever->getAssignedToShipsCount($this->user);
    }

    public function getCrewLimit(): int
    {
        return $this->crewLimitCalculator->getGlobalCrewLimit($this->user);
    }

    public function isCurrentUser(): bool
    {
        return $this->currentUserId === $this->user->getId();
    }

    public function isFounder(): bool
    {
        return $this->allianceJobManager->hasUserPermission($this->user, $this->alliance, AllianceJobPermissionEnum::FOUNDER);
    }

    /**
     * @return array<Colony>
     */
    public function getColonies(): array
    {
        return $this->user->getColonies()->toArray();
    }

    /**
     * @return iterable<array{rump_id: int, amount: int, name: string}>
     */
    public function getShipRumpList(): iterable
    {
        return $this->spacecraftRumpRepository->getGroupedInfoByUser($this->user);
    }

    public function canBeDemoted(): bool
    {
        return $this->isCurrentUser() === false
            && $this->allianceJobManager->hasUserJob($this->user, $this->alliance);
    }

    public function canBeKicked(): bool
    {
        return $this->isFounder() === false
            && $this->isCurrentUser() === false;
    }
}
