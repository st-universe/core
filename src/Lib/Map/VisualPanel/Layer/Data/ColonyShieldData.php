<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class ColonyShieldData extends AbstractData
{
    #[Column(type: 'boolean')]
    private bool $shieldstate = false;

    public function isShielded(): bool
    {
        return $this->shieldstate;
    }
}
