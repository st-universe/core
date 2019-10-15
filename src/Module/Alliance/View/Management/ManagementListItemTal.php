<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class ManagementListItemTal
{
    private $shipRumpRepository;

    private $colonyRepository;

    private $alliance;

    private $user;

    private $currentUserId;

    public function __construct(
        ShipRumpRepositoryInterface $shipRumpRepository,
        ColonyRepositoryInterface $colonyRepository,
        AllianceInterface $alliance,
        UserInterface $user,
        int $currentUserId
    ) {
        $this->user = $user;
        $this->currentUserId = $currentUserId;
        $this->alliance = $alliance;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->colonyRepository = $colonyRepository;
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
        return $this->user->getUser();
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
        return $this->colonyRepository->getOrderedListByUser($this->user);
    }

    public function getShipRumpList(): array
    {
        return $this->shipRumpRepository->getGroupedInfoByUser((int) $this->user->getId());
    }
}
