<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class ManagementListItemTal
{
    private ShipRumpRepositoryInterface $shipRumpRepository;

    private AllianceInterface $alliance;

    private UserInterface $user;

    private int $currentUserId;

    private AllianceJobRepositoryInterface $allianceJobRepository;

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

    public function getId(): int
    {
        return $this->user->getId();
    }

    public function getFaction(): int
    {
        return (int)$this->user->getFactionId();
    }

    public function getName(): string
    {
        return $this->user->getUserName();
    }

    public function getLastActionDate(): int
    {
        return (int)$this->user->getLastAction();
    }

    public function isCurrentUser(): bool
    {
        return $this->currentUserId === $this->user->getId();
    }

    public function isFounder(): bool
    {
        return $this->alliance->getFounder()->getUserId() == $this->user->getId();
    }

    public function getColonies(): array
    {
        return $this->user->getColonies()->toArray();
    }

    public function getShipRumpList(): array
    {
        return $this->shipRumpRepository->getGroupedInfoByUser($this->user);
    }

    /**
     * Returns `true` if the user can be demoted
     */
    public function canBeDemoted(): bool
    {
        $userId = $this->user->getId();

        return $this->currentUserId !== $userId && $this->allianceJobRepository->getByUser($userId) !== [];
    }
}
