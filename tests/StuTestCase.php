<?php

declare(strict_types=1);

namespace Stu;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

abstract class StuTestCase extends MockeryTestCase
{
    /**
     * @template TClass of object
     *
     * @param class-string<TClass> $className
     *
     * @return MockInterface&TClass
     */
    protected function mock(string $className)
    {
        /** @var MockInterface&TClass $result */
        $result = Mockery::mock($className);
        return $result;
    }
}
