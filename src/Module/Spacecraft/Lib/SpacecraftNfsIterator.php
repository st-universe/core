<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Iterator;
use Override;
use Stu\Module\Spacecraft\Lib\TSpacecraftItemInterface;

/**
 * @implements Iterator<SpacecraftNfsItem>
 *
 */
final class SpacecraftNfsIterator implements Iterator
{
    private int $position = 0;

    /** @param array<TSpacecraftItemInterface> $spacecrafts */
    public function __construct(private array $spacecrafts, private int $userId) {}

    #[Override]
    public function rewind(): void
    {
        $this->position = 0;
    }

    #[Override]
    public function current(): SpacecraftNfsItem
    {
        return new SpacecraftNfsItem($this->spacecrafts[$this->position], $this->userId);
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
        return isset($this->spacecrafts[$this->position]);
    }

    public function count(): int
    {
        return count($this->spacecrafts);
    }
}
