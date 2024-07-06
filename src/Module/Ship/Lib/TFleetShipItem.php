<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Override;

#[Entity]
class TFleetShipItem extends TSpacecraftItem implements TFleetShipItemInterface
{
    #[Column(type: 'string')]
    private string $fleet_name = '';

    #[Column(type: 'boolean')]
    private bool $is_defending = false;

    #[Column(type: 'boolean')]
    private bool $is_blocking = false;

    #[Override]
    public function getFleetName(): string
    {
        return $this->fleet_name;
    }

    #[Override]
    public function isDefending(): bool
    {
        return $this->is_defending;
    }

    #[Override]
    public function isBlocking(): bool
    {
        return $this->is_blocking;
    }
}
