<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

/**
 * Service class for wrapping alliance members for UI purposes
 */
final class ManagementListItem
{
    private ShipRumpRepositoryInterface $shipRumpRepository;

    private AllianceInterface $alliance;

    private UserInterface $user;

    private AllianceJobRepositoryInterface $allianceJobRepository;

    private int $currentUserId;

    public function __construct(
        AllianceJobRepositoryInterface $allianceJobRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        AllianceInterface $alliance,
        UserInterface $user,
        int $currentUserId
    ) {
        $this->user = $user;
        $this->currentUserId = $currentUserId;
        $this->alliance = $alliance;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->allianceJobRepository = $allianceJobRepository;
    }

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
        return (int) $this->user->getFactionId();
    }

    /**
     * Return the user's name
     */
    public function getName(): string
    {
        return $this->user->getUserName();
    }

    /**
     * Returns the unix timestamp of the most recent action of the user
     */
    public function getLastActionDate(): int
    {
        return $this->user->getLastAction();
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
        return $this->alliance->getFounder()->getUserId() == $this->user->getId();
    }

    /**
     * @return iterable<ColonyInterface>
     */
    public function getColonies(): iterable
    {
        return $this->user->getColonies();
    }

    /**
     * @return iterable<array{rump_id: int, amount: int, name: string}>
     */
    public function getShipRumpList(): iterable
    {
        return $this->shipRumpRepository->getGroupedInfoByUser($this->user);
    }

    /**
     * Returns `true` if the user can be demoted
     */
    public function canBeDemoted(): bool
    {
        $userId = $this->user->getId();

        return $this->isCurrentUser() === false
            && $this->allianceJobRepository->getByUser($userId) !== [];
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
