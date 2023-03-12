<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class GameSettingsTest extends StuTestCase
{
    /** @var MockInterface|ConfigInterface */
    private ConfigInterface $config;

    /** @var MockObject|GameSettings */
    private GameSettings $subject;

    public function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);

        $this->subject = new GameSettings(null, $this->config);
    }

    public function testgetAdminIdsExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.admins')
            ->once()
            ->andReturn([5, 42]);

        $admins = $this->subject->getAdminIds();

        $this->assertEquals([5, 42], $admins);
    }

    public function testgetAdminIdsExpectDefaultWhenNotPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.admins')
            ->once()
            ->andReturn(null);

        $admins = $this->subject->getAdminIds();

        $this->assertEquals([], $admins);
    }

    public function testgetAdminIdsExpectErrorWhenNotAnArray(): void
    {
        static::expectExceptionMessage('The value "foo" with path "game.admins" is no valid array.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('game.admins')
            ->once()
            ->andReturn('foo');

        $this->subject->getAdminIds();
    }

    public function testGetTempDirExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.temp_dir')
            ->once()
            ->andReturn('folder');

        $tempDir = $this->subject->getTempDir();

        $this->assertEquals('folder', $tempDir);
    }

    public function testGetTempDirExpectErrorWhenValueTooLow(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "game.temp_dir"');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('game.temp_dir')
            ->once()
            ->andReturn(null);

        $this->subject->getTempDir();
    }

    public function testGetVersionExpectIntegerValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.version')
            ->once()
            ->andReturn(1234);

        $version = $this->subject->getVersion();

        $this->assertEquals(1234, $version);
    }

    public function testGetVersionExpectStringValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.version')
            ->once()
            ->andReturn('1234');

        $version = $this->subject->getVersion();

        $this->assertEquals('1234', $version);
    }

    public function testGetVersionExpectErrorWhenNotPresent(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "game.version"');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('game.version')
            ->twice()
            ->andReturn(null);

        $this->subject->getVersion();
    }

    public function testGetWebrootExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.webroot')
            ->once()
            ->andReturn('path');

        $webroot = $this->subject->getWebroot();

        $this->assertEquals('path', $webroot);
    }

    public function testGetWebrootExpectErrorWhenNotPresent(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "game.webroot"');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('game.webroot')
            ->once()
            ->andReturn(null);

        $this->subject->getWebroot();
    }

    public function testGetWebrootExpectErrorWhenTypeWrong(): void
    {
        static::expectExceptionMessage('The value "1" with path "game.webroot" is no valid string.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('game.webroot')
            ->once()
            ->andReturn(true);

        $this->subject->getWebroot();
    }
}
