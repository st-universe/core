<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

use Mockery\MockInterface;
use Monolog\Logger;
use Stu\StuTestCase;

class StuLoggerTest extends StuTestCase
{
    private MockInterface&Logger $mockLogger;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockLogger = $this->mock(Logger::class);

        StuLogger::setMock($this->mockLogger);
    }

    public function testLog(): void
    {
        $this->mockLogger->shouldReceive('info')
            ->with('MESSAGE')
            ->once();

        StuLogger::log('MESSAGE');
    }

    public function testLogf(): void
    {
        $this->mockLogger->shouldReceive('info')
            ->with('MESSAGE with SALT')
            ->once();

        StuLogger::logf('MESSAGE with %s', 'SALT');
    }

    public function testGetLogger(): void
    {
        $result1 = StuLogger::getLogger(LogTypeEnum::ANOMALY);
        $result2 = StuLogger::getLogger(LogTypeEnum::DEFAULT);

        $this->assertSame($this->mockLogger, $result1);
        $this->assertSame($this->mockLogger, $result2);
    }
}
