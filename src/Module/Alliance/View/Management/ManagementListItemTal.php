<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use AllianceData;
use Colony;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use UserData;

final class ManagementListItemTal
{
    private $shipRumpRepository;

    private $alliance;

    private $user;

    private $currentUserId;

    public function __construct(
        ShipRumpRepositoryInterface $shipRumpRepository,
        AllianceData $alliance,
        UserData $user,
        int $currentUserId
    ) {
        $this->user = $user;
        $this->currentUserId = $currentUserId;
        $this->alliance = $alliance;
        $this->shipRumpRepository = $shipRumpRepository;
    }

    public function getId(): int
    {
        return $this->user->getId();
    }

    public function getFaction(): int
    {
        return (int)$this->user->getFaction();
    }

    public function getName(): string
    {
        return $this->user->getName();
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
        return Colony::getListBy('user_id=' . $this->user->getId());
    }

    public function getShipRumpList(): array
    {
        return $this->shipRumpRepository->getGroupedInfoByUser((int) $this->user->getId());
    }
}