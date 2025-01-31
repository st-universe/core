<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\LocationInterface;

interface AnomalyCreationInterface
{
    public function create(
        AnomalyTypeEnum $type,
        ?LocationInterface $location,
        ?AnomalyInterface $parent = null,
        ?Object $dataObject = null
    ): AnomalyInterface;
}
