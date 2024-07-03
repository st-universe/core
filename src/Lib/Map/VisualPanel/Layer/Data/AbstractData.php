<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;

#[MappedSuperclass]
class AbstractData implements CellDataInterface
{
    public function __construct(#[Id]
    #[Column(type: 'integer')]
    private int $x, #[Id]
    #[Column(type: 'integer')]
    private int $y)
    {
    }

    #[Override]
    public function getPosX(): int
    {
        return $this->x;
    }

    #[Override]
    public function getPosY(): int
    {
        return $this->y;
    }
}
