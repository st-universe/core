<?php

declare(strict_types=1);

namespace Stu\Extension;

use Noodlehaus\Config;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExtensionRegistryTest extends TestCase
{
    public function testCoreNeedsNoExtension(): void
    {
        $registry = new ExtensionRegistry(new Config([]), '/missing');

        self::assertSame([], $registry->all());
        self::assertSame([], $registry->entityPaths());
        self::assertSame([], $registry->migrations('pgsql'));
        self::assertSame([], $registry->templates('head.scripts'));
        self::assertSame([], $registry->handlers('interaction'));
        self::assertTrue($registry->managesSchemaAsset('stu_user'));
    }

    public function testDisabledMissingModuleDoesNotLoadOrMapItsTables(): void
    {
        $config = new Config([]);
        $config->set('extensions', ['demo' => ['enabled' => false, 'path' => 'missing', 'tablePrefixes' => ['stu_demo_']]]);
        $registry = new ExtensionRegistry($config, '/missing');

        self::assertSame([], $registry->all());
        self::assertSame([], $registry->entityPaths());
        self::assertFalse($registry->managesSchemaAsset('stu_demo_session'));
        self::assertTrue($registry->managesSchemaAsset('stu_user'));
    }

    public function testEnabledMissingModuleIsSkippedAndDoesNotMapItsTables(): void
    {
        $config = new Config([]);
        $config->set('extensions', ['demo' => ['enabled' => true, 'path' => 'missing', 'tablePrefixes' => ['stu_demo_']]]);

        $registry = new ExtensionRegistry($config, '/missing');
        self::assertSame([], $registry->all());
        self::assertSame([], $registry->migrations('sqlite'));
        self::assertFalse($registry->managesSchemaAsset('stu_demo_session'));
        self::assertTrue($registry->managesSchemaAsset('stu_user'));
    }

    public function testDisabledModuleCannotBeRequested(): void
    {
        $registry = new ExtensionRegistry(new Config([]), '/missing');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Extension demo is disabled or missing');

        $registry->get('demo');
    }
}
