<?php

declare(strict_types=1);

namespace Stu;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use ReflectionClass;
use request;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;

abstract class StuTestCase extends MockeryTestCase
{

    protected function tearDown(): void
    {
        request::setMockVars(null);
    }

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

    protected function getMethod($subject, string $methodName)
    {
        $class = new ReflectionClass($subject);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    protected function initLoggerUtil(): LoggerUtilFactoryInterface
    {
        $loggerUtil = $this->mock(LoggerUtilInterface::class);
        $loggerUtilFactory = $this->mock(LoggerUtilFactoryInterface::class);

        $loggerUtilFactory->shouldReceive('getLoggerUtil')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($loggerUtil);
        $loggerUtil->shouldReceive('init')
            ->withSomeOfArgs()
            ->zeroOrMoreTimes();
        $loggerUtil->shouldReceive('log')
            ->withSomeOfArgs()
            ->zeroOrMoreTimes();

        return $loggerUtilFactory;
    }
}
