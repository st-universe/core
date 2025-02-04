<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Override;

#[MappedSuperclass]
class AbstractData implements CellDataInterface
{
    /** @param null|array<string> $effects */
    public function __construct(
        #[Id]
        #[Column(type: 'integer')]
        private int $x,
        #[Id]
        #[Column(type: 'integer')]
        private int $y,
        #[Column(type: 'json', nullable: true)]
        protected ?array $effects = null
    ) {}

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
