<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class ShipCountData extends AbstractData
{
    #[Column(type: 'integer')]
    private int $shipcount = 0;
    #[Column(type: 'integer')]
    private int $cloakcount = 0;

    public function getShipCount(): int
    {
        return $this->shipcount;
    }

    public function hasCloakedShips(): bool
    {
        return $this->cloakcount > 0;
    }
}
