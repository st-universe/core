<?php

declare(strict_types=1);

namespace Stu\Component\Map;

use Stu\Component\Map\Effects\EffectHandling;
use Stu\Component\Map\Effects\EffectHandlingInterface;
use Stu\Component\Map\Effects\Type\CloakUnuseableEffectHandler;
use Stu\Component\Map\Effects\Type\EpsLeakEffectHandler;
use Stu\Component\Map\Effects\Type\NfsMalfunctionCooldownEffectHandler;
use Stu\Component\Map\Effects\Type\ReactorLeakEffectHandler;
use Stu\Component\Map\Effects\Type\ShieldMalfunctionEffectHandler;
use Stu\Component\Map\Effects\Type\WarpdriveLeakEffectHandler;
use Stu\Lib\Map\FieldTypeEffectEnum;

use function DI\autowire;

return [
    EffectHandlingInterface::class => autowire(EffectHandling::class)
        ->constructorParameter(
            'handlerList',
            [
                FieldTypeEffectEnum::CLOAK_UNUSEABLE->value => autowire(CloakUnuseableEffectHandler::class),
                FieldTypeEffectEnum::WARPDRIVE_LEAK->value => autowire(WarpdriveLeakEffectHandler::class),
                FieldTypeEffectEnum::NFS_MALFUNCTION_COOLDOWN->value => autowire(NfsMalfunctionCooldownEffectHandler::class),
                FieldTypeEffectEnum::SHIELD_MALFUNCTION->value => autowire(ShieldMalfunctionEffectHandler::class),
                FieldTypeEffectEnum::REACTOR_LEAK->value => autowire(ReactorLeakEffectHandler::class),
                FieldTypeEffectEnum::EPS_LEAK->value => autowire(EpsLeakEffectHandler::class)
            ]
        ),
    EncodedMapInterface::class => autowire(EncodedMap::class),
];
