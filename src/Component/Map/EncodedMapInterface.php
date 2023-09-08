<?php

declare(strict_types=1);

namespace Stu\Component\Map;

use Stu\Orm\Entity\LayerInterface;

interface EncodedMapInterface
{
    public function getEncodedMapPath(int $mapFieldType, LayerInterface $layer): string;
}
