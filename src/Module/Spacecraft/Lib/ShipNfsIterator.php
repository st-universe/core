<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Iterator;
use Override;
use Stu\Module\Spacecraft\Lib\TSpacecraftItemInterface;

/**
 * @implements Iterator<ShipNfsItem>
 *
 */
final class ShipNfsIterator implements Iterator
{
    private int $position = 0;

    /** @param array<TSpacecraftItemInterface> $ships */
    public function __construct(private array $ships, private int $userId) {}

    #[Override]
    public function rewind(): void
    {
        $this->position = 0;
    }

    #[Override]
    public function current(): ShipNfsItem
    {
        return new ShipNfsItem($this->ships[$this->position], $this->userId);
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
        return isset($this->ships[$this->position]);
    }

    public function count(): int
    {
        return count($this->ships);
    }
}
