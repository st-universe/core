<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class TFleetShipItem extends TShipItem implements TFleetShipItemInterface
{
    #[Column(type: 'string')]
    private string $fleet_name = '';

    #[Column(type: 'boolean')]
    private bool $is_defending = false;

    #[Column(type: 'boolean')]
    private bool $is_blocking = false;

    public function getFleetName(): string
    {
        return $this->fleet_name;
    }

    public function isDefending(): bool
    {
        return $this->is_defending;
    }

    public function isBlocking(): bool
    {
        return $this->is_blocking;
    }
}
