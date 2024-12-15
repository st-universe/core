<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use RuntimeException;
use Stu\Lib\SessionInterface;
use Stu\Module\Spacecraft\Lib\ShipNfsItem;
use Stu\Module\Spacecraft\Lib\ShipNfsIterator;
use Stu\Module\Ship\Lib\TFleetShipItemInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

final class FleetNfsItem
{
    /** @param array<TFleetShipItemInterface> $ships */
    public function __construct(
        private array $ships,
        private SpacecraftInterface $currentSpacecraft,
        private ?SessionInterface $session,
        private int $userId
    ) {}

    public function isHidden(): bool
    {
        return $this->session !== null && $this->session->hasSessionValue('hiddenfleets', $this->getId());
    }

    public function getVisibleShips(): ShipNfsIterator
    {
        return new ShipNfsIterator($this->ships, $this->userId);
    }

    public function getVisibleShipsCount(): int
    {
        return count($this->ships);
    }

    public function isFleetOfCurrentShip(): bool
    {
        $currentFleet = $this->currentSpacecraft instanceof ShipInterface ? $this->currentSpacecraft->getFleet() : null;
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

    public function getLeadShip(): ShipNfsItem
    {
        return new ShipNfsItem($this->ships[0], $this->userId);
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
