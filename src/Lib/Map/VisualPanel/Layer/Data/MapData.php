<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class MapData extends AbstractData
{
    #[Column(type: 'integer')]
    private int $type = 0;

    public function __construct(int $x, int $y, int $type)
    {
        parent::__construct($x, $y);

        $this->type = $type;
    }

    public function getMapfieldType(): int
    {
        return $this->type;
    }
}
