<?php

declare(strict_types=1);

namespace Stu\Extension;

use Noodlehaus\Config;
use RuntimeException;
use Stu\StuTestCase;

final class ExtensionRegistryTest extends StuTestCase
{
    public function testRegistryCanReadManifestAfterInstallerHasLoadedIt(): void
    {
        $root = sys_get_temp_dir() . '/stu-extension-registry-' . bin2hex(random_bytes(8));
        $moduleDirectory = $root . '/demo';
        mkdir($moduleDirectory, 0750, true);
        $manifestFile = $moduleDirectory . '/module.php';
        file_put_contents($manifestFile, '<?php return ' . var_export([
            'id' => 'demo',
            'apiVersion' => ExtensionRegistry::API_VERSION,
        ], true) . ';');

        try {
            self::assertIsArray(ExtensionManifestLoader::load($manifestFile));

            $config = new Config([]);
            $config->set('extensions', ['demo' => ['enabled' => true, 'path' => 'demo']]);
            $registry = new ExtensionRegistry($config, $root);

            self::assertSame('demo', $registry->get('demo')['id']);
        } finally {
            unlink($manifestFile);
            rmdir($moduleDirectory);
            rmdir($root);
        }
    }

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
