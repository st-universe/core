<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

enum FieldTypeEffectEnum: string
{
    case CLOAK_UNUSEABLE = 'CLOAK_UNUSEABLE';
    case WARPDRIVE_LEAK = 'WARPDRIVE_LEAK';
    case LSS_MALFUNCTION = 'LSS_MALFUNCTION';
}
