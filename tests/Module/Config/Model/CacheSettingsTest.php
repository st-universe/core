<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class CacheSettingsTest extends StuTestCase
{
    /** @var MockInterface|ConfigInterface */
    private ConfigInterface $config;

    /** @var MockObject|CacheSettings */
    private CacheSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);

        $this->subject = new CacheSettings(null, $this->config);
    }

    public function testUseRedisExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('cache.useRedis')
            ->once()
            ->andReturn(false);

        $useRedis = $this->subject->useRedis();

        $this->assertFalse($useRedis);
    }

    public function testUseRedisExpectDefaultWhenNotPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('cache.useRedis')
            ->once()
            ->andReturn(null);

        $useRedis = $this->subject->useRedis();

        $this->assertTrue($useRedis);
    }

    public function testUseRedisExpectErrorWhenWrongType(): void
    {
        static::expectExceptionMessage('The value "123" with path "cache.useRedis" is no valid boolean.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('cache.useRedis')
            ->once()
            ->andReturn(123);

        $this->subject->useRedis();
    }

    //SOCKET
    public function testGetRedisSocketExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('cache.redis_socket')
            ->twice()
            ->andReturn('test');

        $namespace = $this->subject->getRedisSocket();

        $this->assertEquals('test', $namespace);
    }

    public function testGetRedisSocketExpectEmptyStringWhenNotPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('cache.redis_socket')
            ->once()
            ->andReturn(null);

        $this->assertEquals('', $this->subject->getRedisSocket());
    }

    public function testGetRedisSocketExpectErrorWhenWrongType(): void
    {
        static::expectExceptionMessage('The value "123" with path "cache.redis_socket" is no valid string.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('cache.redis_socket')
            ->twice()
            ->andReturn(123);

        $this->subject->getRedisSocket();
    }

    //HOST
    public function testGetRedisHostExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('cache.redis_host')
            ->once()
            ->andReturn('test');

        $namespace = $this->subject->getRedisHost();

        $this->assertEquals('test', $namespace);
    }

    public function testGetRedisHostExpectErrorWhenNotPresent(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "cache.redis_host"');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('cache.redis_host')
            ->once()
            ->andReturn(null);

        $this->subject->getRedisHost();
    }

    public function testGetRedisHostExpectErrorWhenWrongType(): void
    {
        static::expectExceptionMessage('The value "123" with path "cache.redis_host" is no valid string.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('cache.redis_host')
            ->once()
            ->andReturn(123);

        $this->subject->getRedisHost();
    }

    //PORT
    public function testGetRedisPortExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('cache.redis_port')
            ->once()
            ->andReturn(42);

        $namespace = $this->subject->getRedisPort();

        $this->assertEquals(42, $namespace);
    }

    public function testGetRedisPortExpectErrorWhenNotPresent(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "cache.redis_port"');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('cache.redis_port')
            ->once()
            ->andReturn(null);

        $this->subject->getRedisPort();
    }

    public function testGetRedisPortExpectErrorWhenWrongType(): void
    {
        static::expectExceptionMessage('The value "foobar" with path "cache.redis_port" is no valid integer.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('cache.redis_port')
            ->once()
            ->andReturn('foobar');

        $this->subject->getRedisPort();
    }
}
