<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Lock;

use Stu\Exception\InvalidParamException;

final class LockEnum
{
    public const LOCK_TYPE_COLONY_GROUP = 1;

    public static function getLockPathIdentifier(int $lockType): string
    {
        switch ($lockType) {
            case self::LOCK_TYPE_COLONY_GROUP:
                return 'colonyGroup';
            default:
                throw new InvalidParamException('lockType does not exist');
        }
    }
}
