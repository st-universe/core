<?php

declare(strict_types=1);

namespace Stu;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

abstract class StuTestCase extends MockeryTestCase
{
    protected function mock(...$args): MockInterface
    {
        return call_user_func_array(
            [\Mockery::class, 'mock'],
            $args
        );
    }
}
