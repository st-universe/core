<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Iterator;
use Stu\Lib\SessionInterface;
use Stu\Orm\Entity\ShipInterface;

/**
 * @template TShipItem of array{
 *  fleetid: int,
 *  fleetname: string,
 *  isdefending: bool,
 *  isblocking: bool,
 *  shipid: int,
 *  rumpid: int,
 *  formerrumpid: int,
 *  warpstate: int,
 *  cloakstate: int,
 *  shieldstate: int,
 *  uplinkstate: int,
 *  isdestroyed: bool,
 *  spacecrafttype: int,
 *  shipname: string,
 *  hull: int,
 *  maxhull: int,
 *  shield: int,
 *  webid: int,
 *  webfinishtime: int,
 *  userid: int,
 *  username: string,
 *  rumpcategoryid: int,
 *  rumpname: string,
 *  rumproleid: int,
 *  haslogbook: bool
 * }
 */
final class FleetNfsIterator implements Iterator
{
    private ShipInterface $currentShip;

    private ?SessionInterface $session;

    private int $userId;

    protected int $position = 0;

    /** @var array<int, TShipItem> */
    private array $fleets = [];

    /**
     * @param iterable<TShipItem> $ships
     */
    public function __construct(iterable $ships, ShipInterface $currentShip, ?SessionInterface $session, int $userId)
    {
        $this->currentShip = $currentShip;
        $this->session = $session;
        $this->userId = $userId;

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

    public function current(): FleetNfsItem
    {
        return new FleetNfsItem($this->fleets[$this->position], $this->currentShip, $this->session, $this->userId);
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
