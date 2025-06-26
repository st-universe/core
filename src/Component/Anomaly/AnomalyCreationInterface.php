<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\Location;

interface AnomalyCreationInterface
{
    public function create(
        AnomalyTypeEnum $type,
        ?Location $location,
        ?Anomaly $parent = null,
        ?Object $dataObject = null
    ): Anomaly;
}
