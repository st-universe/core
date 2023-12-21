<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

interface CellDataInterface
{
    public function getPosX(): int;

    public function getPosY(): int;
}
