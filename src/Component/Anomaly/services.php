<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Stu\Component\Anomaly\Type\AdventDoorHandler;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Anomaly\Type\SubspaceEllipseHandler;

use function DI\autowire;

return [
    AnomalyCreationInterface::class => autowire(AnomalyCreation::class),
    AnomalyHandlingInterface::class => autowire(AnomalyHandling::class)
        ->constructorParameter(
            'handlerList',
            [
                AnomalyTypeEnum::SUBSPACE_ELLIPSE->value => autowire(SubspaceEllipseHandler::class),
                AnomalyTypeEnum::SPECIAL_ADVENT_DOOR->value => autowire(AdventDoorHandler::class)
            ]
        )
];
