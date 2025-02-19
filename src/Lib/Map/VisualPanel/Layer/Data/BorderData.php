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
    #[Column(type: 'string', nullable: true)]
    private ?string $impassable = null;
    #[Column(type: 'string', nullable: true)]
    private ?string $normal = null;
    #[Column(type: 'string', nullable: true)]
    private ?string $cartographing = null;

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

    public function getImpassableColor(): ?string
    {
        return $this->impassable;
    }

    public function getNormalColor(): ?string
    {
        return $this->normal;
    }

    public function getCartographingColor(): ?string
    {
        return $this->cartographing;
    }
}