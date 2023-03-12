<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Lib\SessionInterface;
use Stu\Orm\Entity\ShipInterface;

final class FleetNfsItem
{
    private array $ships;

    private ShipInterface $currentShip;

    private ?SessionInterface $session;

    private int $userId;

    public function __construct(
        array $ships,
        ShipInterface $currentShip,
        ?SessionInterface $session,
        int $userId
    ) {
        $this->ships = $ships;
        $this->session = $session;
        $this->currentShip = $currentShip;
        $this->userId = $userId;
    }

    public function isHidden(): bool
    {
        return $this->session !== null && $this->session->hasSessionValue('hiddenfleets', $this->getId());
    }

    public function getVisibleShips(): iterable
    {
        return new ShipNfsIterator($this->ships, $this->userId);
    }

    public function getVisibleShipsCount(): int
    {
        return count($this->ships);
    }

    public function isFleetOfCurrentShip(): bool
    {
        return $this->currentShip->getFleet()->getId() === $this->ships[0]['fleetid'];
    }

    public function showManagement(): bool
    {
        return $this->currentShip->getUser()->getId() === $this->ships[0]['userid'];
    }

    public function getName(): string
    {
        return $this->ships[0]['fleetname'];
    }

    public function getId(): int
    {
        return $this->ships[0]['fleetid'];
    }

    public function getLeadShip(): ShipNfsItem
    {
        return new ShipNfsItem($this->ships[0], $this->userId);
    }

    public function getUserId(): int
    {
        return $this->ships[0]['userid'];
    }

    public function getUserName(): string
    {
        return $this->ships[0]['username'];
    }

    public function getDefendedColony(): bool
    {
        return $this->ships[0]['isdefending'];
    }

    public function getBlockedColony(): bool
    {
        return  $this->ships[0]['isblocking'];
    }
}
