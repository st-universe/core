<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

final class TrumfieldNfsItem
{
    public function __construct(private TTrumfieldItem $item) {}

    public function getHull(): int
    {
        return $this->item->getHull();
    }

    public function isTrumfield(): bool
    {
        return true;
    }

    public function getFormerRumpId(): int
    {
        return $this->item->getFormerRumpId();
    }
}
