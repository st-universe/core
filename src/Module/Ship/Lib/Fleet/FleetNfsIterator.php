<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use Iterator;
use Override;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Ship\Lib\TFleetShipItemInterface;
use Stu\Orm\Entity\SpacecraftInterface;

/**
 * @implements Iterator<FleetNfsItem>
 *
 */
final class FleetNfsIterator implements Iterator
{
    private int $position = 0;

    /** @var array<int, array<TFleetShipItemInterface>> */
    private array $fleets = [];

    /**
     * @param iterable<TFleetShipItemInterface> $ships
     */
    public function __construct(
        iterable $ships,
        private SpacecraftInterface $currentSpacecraft,
        private ?SessionStorageInterface $sessionStorage,
        private int $userId
    ) {
        $currentFleetId = null;
        $currentFleet = null;

        foreach ($ships as $ship) {
            $newFleetId = $ship->getFleetId();

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

        if ($currentFleet !== null) {
            $this->fleets[] = $currentFleet;
        }
    }

    #[Override]
    public function rewind(): void
    {
        $this->position = 0;
    }

    #[Override]
    public function current(): FleetNfsItem
    {
        return new FleetNfsItem($this->fleets[$this->position], $this->currentSpacecraft, $this->sessionStorage, $this->userId);
    }

    #[Override]
    public function key(): int
    {
        return $this->position;
    }

    #[Override]
    public function next(): void
    {
        ++$this->position;
    }

    #[Override]
    public function valid(): bool
    {
        return isset($this->fleets[$this->position]);
    }

    public function count(): int
    {
        return count($this->fleets);
    }
}
