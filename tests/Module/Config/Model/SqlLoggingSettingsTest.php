<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class SqlLoggingSettingsTest extends StuTestCase
{
    /** @var MockInterface|ConfigInterface */
    private ConfigInterface $config;

    /** @var MockObject|SqlLoggingSettings */
    private SqlLoggingSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);

        $this->subject = new SqlLoggingSettings(null, $this->config);
    }

    public function testIsActiveExpectDefaultWhenNotPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('sqlLogging.isActive')
            ->once()
            ->andReturn(null);

        $isDebugMode = $this->subject->isActive();

        $this->assertFalse($isDebugMode);
    }

    public function testIsActiveExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('sqlLogging.isActive')
            ->once()
            ->andReturn(true);

        $isDebugMode = $this->subject->isActive();

        $this->assertTrue($isDebugMode);
    }

    public function testIsDebugModeExpectErrorWhenWrongType(): void
    {
        static::expectExceptionMessage('The value "123" with path "sqlLogging.isActive" is no valid boolean.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('sqlLogging.isActive')
            ->once()
            ->andReturn(123);

        $this->subject->isActive();
    }

    public function testGetLogDirectoryExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('sqlLogging.logDirectory')
            ->once()
            ->andReturn('/foo/bar');

        $path = $this->subject->getLogDirectory();

        $this->assertEquals('/foo/bar', $path);
    }

    public function testGetLogDirectoryExpectErrorWhenNotPresent(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "sqlLogging.logDirectory"');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('sqlLogging.logDirectory')
            ->once()
            ->andReturn(null);

        $this->subject->getLogDirectory();
    }

    public function testGetLogDirectoryExpectErrorWhenWrongType(): void
    {
        static::expectExceptionMessage('The value "123" with path "sqlLogging.logDirectory" is no valid string.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('sqlLogging.logDirectory')
            ->once()
            ->andReturn(123);

        $this->subject->getLogDirectory();
    }
}
