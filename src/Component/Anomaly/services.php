<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Anomaly\Type\SubspaceEllipseHandler;

use function DI\autowire;
use function DI\get;

return [
    'anomaly_handler' => [
        AnomalyTypeEnum::ANOMALY_TYPE_SUBSPACE_ELLIPSE => autowire(SubspaceEllipseHandler::class)
    ],
    AnomalyCreationInterface::class => autowire(AnomalyCreation::class),
    AnomalyHandlingInterface::class => autowire(AnomalyHandling::class)
        ->constructorParameter(
            'handlerList',
            get('anomaly_handler')
        )
];
