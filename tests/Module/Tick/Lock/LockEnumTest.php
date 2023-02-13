<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Lock;

use Exception;
use Stu\StuTestCase;

class LockEnumTest extends StuTestCase
{
    public function testGetLockPathIdentifierOnUnknownLockType(): void
    {
        $errorMessage = 'lockType does not exist';

        static::expectException(Exception::class);
        static::expectExceptionMessage($errorMessage);

        LockEnum::getLockPathIdentifier(424242);
    }

    public function testGetLockGroupConfigPathOnUnknownLockType(): void
    {
        $errorMessage = 'lockType does not exist';

        static::expectException(Exception::class);
        static::expectExceptionMessage($errorMessage);

        LockEnum::getLockGroupConfigPath(424242);
    }
}
