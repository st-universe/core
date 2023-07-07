<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface AnomalyCreationInterface
{
    public function create(
        int $anomalyType,
        MapInterface|StarSystemMapInterface $map
    ): void;
}
