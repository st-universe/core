<?php

declare(strict_types=1);

namespace Stu\Component\Map;

use Stu\Orm\Entity\Layer;

interface EncodedMapInterface
{
    public function getEncodedMapPath(int $mapFieldType, Layer $layer): string;
}
