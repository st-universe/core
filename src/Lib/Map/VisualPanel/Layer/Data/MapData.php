<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class MapData extends AbstractData
{
    public function __construct(int $x, int $y, #[Column(type: 'integer')]
    private int $type)
    {
        parent::__construct($x, $y);
    }

    public function getMapfieldType(): int
    {
        return $this->type;
    }
}
