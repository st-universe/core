<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Override;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class AdminSettingsTest extends StuTestCase
{
    /** @var MockInterface|ConfigInterface */
    private ConfigInterface $config;

    /** @var MockObject|AdminSettings */
    private AdminSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);

        $this->subject = new AdminSettings("game", $this->config);
    }

    //ID
    public function testGetIdExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.admin.id')
            ->once()
            ->andReturn(42);

        $result = $this->subject->getId();

        $this->assertEquals(42, $result);
    }

    public function testGetIdExpectErrorWhenNotPresent(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "game.admin.id"');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('game.admin.id')
            ->once()
            ->andReturnNull();

        $this->subject->getId();
    }

    public function testGetIdExpectErrorWhenNotAnInteger(): void
    {
        static::expectExceptionMessage('The value "foo" with path "game.admin.id" is no valid integer.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('game.admin.id')
            ->once()
            ->andReturn('foo');

        $this->subject->getId();
    }

    //EMAIL
    public function testGetEmailExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.admin.email')
            ->once()
            ->andReturn('foo@bar.de');

        $result = $this->subject->getEmail();

        $this->assertEquals('foo@bar.de', $result);
    }

    public function testGetEmailExpectErrorWhenNotPresent(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "game.admin.email"');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('game.admin.email')
            ->once()
            ->andReturnNull();

        $this->subject->getEmail();
    }

    public function testGetEmailExpectErrorWhenNotAString(): void
    {
        static::expectExceptionMessage('The value "123" with path "game.admin.email" is no valid string.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('game.admin.email')
            ->once()
            ->andReturn(123);

        $this->subject->getEmail();
    }
}
