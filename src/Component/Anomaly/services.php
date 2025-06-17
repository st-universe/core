<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Stu\Component\Anomaly\Type\AdventDoorHandler;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Anomaly\Type\EasterEggHandler;
use Stu\Component\Anomaly\Type\IonStorm\IonStormHandler;
use Stu\Component\Anomaly\Type\IonStorm\IonStormMovement;
use Stu\Component\Anomaly\Type\IonStorm\IonStormPropagation;
use Stu\Component\Anomaly\Type\IonStorm\LocationPoolFactory;
use Stu\Component\Anomaly\Type\SubspaceEllipseHandler;

use function DI\autowire;

return [
    AnomalyCreationInterface::class => autowire(AnomalyCreation::class),
    AnomalyHandlingInterface::class => autowire(AnomalyHandling::class)
        ->constructorParameter(
            'handlerList',
            [
                AnomalyTypeEnum::SUBSPACE_ELLIPSE->value => autowire(SubspaceEllipseHandler::class),
                AnomalyTypeEnum::ION_STORM->value => autowire(IonStormHandler::class)
                    ->constructorParameter('locationPoolFactory', autowire(LocationPoolFactory::class))
                    ->constructorParameter('ionStormPropagation', autowire(IonStormPropagation::class))
                    ->constructorParameter('ionStormMovement', autowire(IonStormMovement::class)),
                AnomalyTypeEnum::SPECIAL_ADVENT_DOOR->value => autowire(AdventDoorHandler::class),
                AnomalyTypeEnum::SPECIAL_EASTER_EGG->value => autowire(EasterEggHandler::class)
            ]
        )
];
