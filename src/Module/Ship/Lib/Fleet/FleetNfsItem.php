<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use RuntimeException;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftNfsItem;
use Stu\Module\Spacecraft\Lib\SpacecraftNfsIterator;
use Stu\Module\Ship\Lib\TFleetShipItemInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;

final class FleetNfsItem
{
    /** @param array<TFleetShipItemInterface> $ships */
    public function __construct(
        private array $ships,
        private Spacecraft $currentSpacecraft,
        private ?SessionStorageInterface $sessionStorage,
        private int $userId
    ) {}

    public function isHidden(): bool
    {
        return $this->sessionStorage !== null && $this->sessionStorage->hasSessionValue('hiddenfleets', $this->getId());
    }

    public function getVisibleShips(): SpacecraftNfsIterator
    {
        return new SpacecraftNfsIterator($this->ships, $this->userId);
    }

    public function getVisibleShipsCount(): int
    {
        return count($this->ships);
    }

    public function isFleetOfCurrentShip(): bool
    {
        $currentFleet = $this->currentSpacecraft instanceof Ship ? $this->currentSpacecraft->getFleet() : null;
        if ($currentFleet === null) {
            throw new RuntimeException('should not happen');
        }

        return $currentFleet->getId() === $this->getId();
    }

    public function showManagement(): bool
    {
        return $this->currentSpacecraft->getUser()->getId() === $this->getUserId();
    }

    public function getName(): string
    {
        return $this->ships[0]->getFleetName();
    }

    public function getId(): int
    {
        return $this->ships[0]->getFleetId() ?? 0;
    }

    public function getLeadShip(): SpacecraftNfsItem
    {
        return new SpacecraftNfsItem($this->ships[0], $this->userId);
    }

    public function getUserId(): int
    {
        return $this->ships[0]->getUserId();
    }

    public function getUserName(): string
    {
        return $this->ships[0]->getUserName();
    }

    public function getDefendedColony(): bool
    {
        return $this->ships[0]->isDefending();
    }

    public function getBlockedColony(): bool
    {
        return  $this->ships[0]->isBlocking();
    }
}
