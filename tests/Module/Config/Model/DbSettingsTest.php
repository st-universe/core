<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class DbSettingsTest extends StuTestCase
{
    /** @var MockInterface|ConfigInterface */
    private ConfigInterface $config;

    /** @var MockObject|DbSettings */
    private DbSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);

        $this->subject = new DbSettings(null, $this->config);
    }

    public function testUseSqliteExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('db.useSqlite')
            ->once()
            ->andReturn(true);

        $useSqlite = $this->subject->useSqlite();

        $this->assertTrue($useSqlite);
    }

    public function testUseSqliteExpectDefaultWhenNotPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('db.useSqlite')
            ->once()
            ->andReturn(null);

        $useSqlite = $this->subject->useSqlite();

        $this->assertFalse($useSqlite);
    }

    public function testUseSqliteExpectErrorWhenWrongType(): void
    {
        static::expectExceptionMessage('The value "123" with path "db.useSqlite" is no valid boolean.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('db.useSqlite')
            ->once()
            ->andReturn(123);

        $this->subject->useSqlite();
    }

    //DATABASE
    public function testGetDatabaseExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('db.database')
            ->once()
            ->andReturn('test');

        $namespace = $this->subject->getDatabase();

        $this->assertEquals('test', $namespace);
    }

    public function testGetDatabaseExpectErrorWhenNotPresent(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "db.database"');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('db.database')
            ->once()
            ->andReturn(null);

        $this->subject->getDatabase();
    }

    public function testGetDatabaseExpectErrorWhenWrongType(): void
    {
        static::expectExceptionMessage('The value "123" with path "db.database" is no valid string.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('db.database')
            ->once()
            ->andReturn(123);

        $this->subject->getDatabase();
    }

    //PROXY-NAMESPACE
    public function testGetProxyNamespaceExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('db.proxy_namespace')
            ->once()
            ->andReturn('test');

        $namespace = $this->subject->getProxyNamespace();

        $this->assertEquals('test', $namespace);
    }

    public function testGetProxyNamespaceExpectErrorWhenNotPresent(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "db.proxy_namespace"');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('db.proxy_namespace')
            ->once()
            ->andReturn(null);

        $this->subject->getProxyNamespace();
    }

    public function testGetProxyNamespaceExpectErrorWhenWrongType(): void
    {
        static::expectExceptionMessage('The value "123" with path "db.proxy_namespace" is no valid string.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('db.proxy_namespace')
            ->once()
            ->andReturn(123);

        $this->subject->getProxyNamespace();
    }
}
