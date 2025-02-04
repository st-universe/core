<?php

declare(strict_types=1);

namespace Stu\Component\Map;

use Stu\Component\Map\Effects\EffectHandling;
use Stu\Component\Map\Effects\EffectHandlingInterface;
use Stu\Component\Map\Effects\Type\CloakUnuseableEffectHandler;
use Stu\Component\Map\Effects\Type\LSSMalfunctionEffectHandler;
use Stu\Component\Map\Effects\Type\WarpdriveLeakEffectHandler;
use Stu\Lib\Map\FieldTypeEffectEnum;

use function DI\autowire;

return [
    EffectHandlingInterface::class => autowire(EffectHandling::class)
        ->constructorParameter(
            'handlerList',
            [
                FieldTypeEffectEnum::CLOAK_UNUSEABLE->value => autowire(CloakUnuseableEffectHandler::class),
                FieldTypeEffectEnum::WARPDRIVE_LEAK->value => autowire(WarpdriveLeakEffectHandler::class)
            ]
        ),
    EncodedMapInterface::class => autowire(EncodedMap::class),
];
