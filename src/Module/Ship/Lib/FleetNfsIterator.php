<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Iterator;
use Stu\Lib\SessionInterface;
use Stu\Orm\Entity\ShipInterface;

final class FleetNfsIterator implements Iterator
{
    private ShipInterface $currentShip;

    private SessionInterface $session;

    protected int $position = 0;

    private array $fleets = [];


    public function __construct(array $ships, $currentShip, SessionInterface $session)
    {
        $this->currentShip = $currentShip;
        $this->session = $session;

        $currentFleetId = null;
        $currentFleet = null;

        foreach ($ships as $ship) {
            $newFleetId = $ship['fleetid'];

            if ($newFleetId !== $currentFleetId) {
                if ($currentFleet !== null) {
                    $this->fleets[] = $currentFleet;
                }

                $currentFleet = [];
                $currentFleet[] = $ship;
                $currentFleetId = $newFleetId;
            } else {
                $currentFleet[] = $ship;
            }
        }

        if (!empty($currentFleet)) {
            $this->fleets[] = $currentFleet;
        }
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): FleetNfsItemNew
    {
        return new FleetNfsItemNew($this->fleets[$this->position], $this->currentShip, $this->session);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->fleets[$this->position]);
    }

    public function count(): int
    {
        return count($this->fleets);
    }
}
