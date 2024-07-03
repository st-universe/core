<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Override;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class DebugSettingsTest extends StuTestCase
{
    /** @var MockInterface|ConfigInterface */
    private ConfigInterface $config;

    /** @var MockObject|DebugSettings */
    private DebugSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);

        $this->subject = new DebugSettings(null, $this->config);
    }

    public function testIsDebugModeExpectDefaultWhenNotPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('debug.debug_mode')
            ->once()
            ->andReturn(null);

        $isDebugMode = $this->subject->isDebugMode();

        $this->assertTrue($isDebugMode);
    }

    public function testIsDebugModeExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('debug.debug_mode')
            ->once()
            ->andReturn(false);

        $isDebugMode = $this->subject->isDebugMode();

        $this->assertFalse($isDebugMode);
    }

    public function testIsDebugModeExpectErrorWhenWrongType(): void
    {
        static::expectExceptionMessage('The value "123" with path "debug.debug_mode" is no valid boolean.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('debug.debug_mode')
            ->once()
            ->andReturn(123);

        $this->subject->isDebugMode();
    }

    public function testGetLogfilePathExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('debug.logfile_path')
            ->once()
            ->andReturn('/foo/bar');

        $path = $this->subject->getLogfilePath();

        $this->assertEquals('/foo/bar', $path);
    }

    public function testGetLogfilePathExpectErrorWhenNotPresent(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "debug.logfile_path"');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('debug.logfile_path')
            ->once()
            ->andReturn(null);

        $this->subject->getLogfilePath();
    }

    public function testGetLogfilePathExpectErrorWhenWrongType(): void
    {
        static::expectExceptionMessage('The value "123" with path "debug.logfile_path" is no valid string.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('debug.logfile_path')
            ->once()
            ->andReturn(123);

        $this->subject->getLogfilePath();
    }

    public function testGetLoglevelExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('debug.loglevel')
            ->once()
            ->andReturn(42);

        $level = $this->subject->getLoglevel();

        $this->assertEquals(42, $level);
    }

    public function testGetLoglevelExpectErrorWhenNotPresent(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "debug.loglevel"');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('debug.loglevel')
            ->once()
            ->andReturn(null);

        $this->subject->getLoglevel();
    }

    public function testGetLoglevelExpectErrorWhenWrongType(): void
    {
        static::expectExceptionMessage('The value "foo" with path "debug.loglevel" is no valid integer.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('debug.loglevel')
            ->once()
            ->andReturn("foo");

        $this->subject->getLoglevel();
    }
}
