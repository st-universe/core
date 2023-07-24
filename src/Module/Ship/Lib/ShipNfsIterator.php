<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Iterator;

/**
 * @implements Iterator<ShipNfsItem>
 * 
 */
final class ShipNfsIterator implements Iterator
{
    protected int $position = 0;

    /** @var array<TShipItemInterface> */
    private array $ships;

    private int $userId;

    /** @param array<TShipItemInterface> $ships */
    public function __construct(array $ships, int $userId)
    {
        $this->ships = $ships;
        $this->userId = $userId;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): ShipNfsItem
    {
        return new ShipNfsItem($this->ships[$this->position], $this->userId);
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
        return isset($this->ships[$this->position]);
    }

    public function count(): int
    {
        return count($this->ships);
    }
}
