<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Iterator;

final class ShipNfsIterator implements Iterator
{
    protected int $position = 0;

    private array $ships;

    private int $userId;

    public function __construct(array $ships, $userId)
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
