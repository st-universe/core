<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class MapSettingsTest extends StuTestCase
{
    /** @var MockInterface|ConfigInterface */
    private ConfigInterface $config;

    /** @var MockObject|MapSettings */
    private MapSettings $subject;

    public function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);

        $this->subject = new MapSettings('game', $this->config);
    }

    public function testGetEncryptionKeyExpectNullWhenNotPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.map.encryptionKey')
            ->andReturn(null);

        $key = $this->subject->getEncryptionKey();

        $this->assertNull($key);
    }

    public function testGetEncryptionKeyExpectStringWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.map.encryptionKey')
            ->andReturn("KEY");

        $key = $this->subject->getEncryptionKey();

        $this->assertEquals("KEY", $key);
    }
}
