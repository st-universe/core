<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

/**
 * Service class for wrapping alliance members for UI purposes
 */
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

    /**
     * Return the user's id
     */
    public function getId(): int
    {
        return $this->user->getId();
    }

    /**
     * Return the user's faction ic
     */
    public function getFaction(): int
    {
        return $this->user->getFactionId();
    }

    /**
     * Return the user's name
     */
    public function getName(): string
    {
        return $this->user->getName();
    }

    /**
     * Returns the unix timestamp of the most recent action of the user
     */
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

    /**
     * Returns `true` if the user matches the game's current user context
     */
    public function isCurrentUser(): bool
    {
        return $this->currentUserId === $this->user->getId();
    }

    /**
     * Returns `true` if the user is the founder of the alliance
     */
    public function isFounder(): bool
    {
        return $this->allianceJobManager->hasUserFounderPermission($this->user, $this->alliance);
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

    /**
     * Returns `true` if the user can be demoted
     */
    public function canBeDemoted(): bool
    {
        return $this->isCurrentUser() === false
            && $this->allianceJobManager->hasUserJob($this->user, $this->alliance);
    }

    /**
     * Returns `true` if the user can be kicked
     */
    public function canBeKicked(): bool
    {
        return $this->isFounder() === false
            && $this->isCurrentUser() === false;
    }
}
