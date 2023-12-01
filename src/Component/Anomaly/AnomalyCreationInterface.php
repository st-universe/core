<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface AnomalyCreationInterface
{
    public function create(
        AnomalyTypeEnum $type,
        MapInterface|StarSystemMapInterface $map
    ): AnomalyInterface;
}
