<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class BorderData extends AbstractData
{
    #[Column(type: 'string', nullable: true)]
    private ?string $allycolor = null;
    #[Column(type: 'string', nullable: true)]
    private ?string $usercolor = null;
    #[Column(type: 'string', nullable: true)]
    private ?string $factioncolor = null;

    public function getAllyColor(): ?string
    {
        return $this->allycolor;
    }

    public function getFactionColor(): ?string
    {
        return $this->factioncolor;
    }

    public function getUserColor(): ?string
    {
        return $this->usercolor;
    }
}
