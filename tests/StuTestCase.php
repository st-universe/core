<?php

declare(strict_types=1);

namespace Stu;

use Override;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use ReflectionClass;
use request;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Logging\PirateLoggerInterface;

abstract class StuTestCase extends MockeryTestCase
{

    #[Override]
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
        $loggerUtil = $this->initLogger();
        $loggerUtilFactory = $this->mock(LoggerUtilFactoryInterface::class);

        $pirateLogger = $this->mock(PirateLoggerInterface::class);

        $loggerUtilFactory->shouldReceive('getLoggerUtil')
            ->zeroOrMoreTimes()
            ->andReturn($loggerUtil);
        $loggerUtilFactory->shouldReceive('getPirateLogger')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($pirateLogger);

        $pirateLogger->shouldReceive('log')
            ->withSomeOfArgs()
            ->zeroOrMoreTimes();
        $pirateLogger->shouldReceive('logf')
            ->withSomeOfArgs()
            ->zeroOrMoreTimes();

        return $loggerUtilFactory;
    }

    protected function initLogger(): LoggerUtilInterface
    {
        $loggerUtil = $this->mock(LoggerUtilInterface::class);

        $loggerUtil->shouldReceive('init')
            ->withSomeOfArgs()
            ->zeroOrMoreTimes();
        $loggerUtil->shouldReceive('log')
            ->withSomeOfArgs()
            ->zeroOrMoreTimes();

        return $loggerUtil;
    }
}
