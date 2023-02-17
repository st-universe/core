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
}
