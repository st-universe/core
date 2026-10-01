<?php

declare(strict_types=1);

namespace Stu\Extension;

use Noodlehaus\Config;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExtensionDeployerTest extends TestCase
{
    private string $root;
    private array $commands = [];
    private string $revision;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/stu-extension-test-' . bin2hex(random_bytes(8));
        mkdir($this->root);
        $this->revision = str_repeat('a', 40);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->root);
    }

    public function testDisabledAndLocalModulesNeverAccessNetwork(): void
    {
        $settings = $this->settings();
        $settings['demo']['enabled'] = false;
        $settings['local'] = ['enabled' => true, 'path' => '../local'];
        $deployer = new ExtensionDeployer($this->root, function (): string {
            self::fail('No deployment commands expected');
        });
        self::assertSame(['changed' => false, 'messages' => []], $deployer->deploy($settings));
        self::assertDirectoryDoesNotExist($this->root . '/var');
    }

    public function testAccessFailureSkipsModuleAndThrottlesRetries(): void
    {
        $calls = 0;
        $deployer = new ExtensionDeployer($this->root, function () use (&$calls): string {
            $calls++;
            throw new RuntimeException('Access denied');
        });
        $result = $deployer->deploy($this->settings());
        self::assertFalse($result['changed']);
        self::assertStringContainsString('demo skipped: Access denied', $result['messages'][0]);
        self::assertFileDoesNotExist($this->root . '/var/extensions/demo/current/module.php');
        self::assertSame(['changed' => false, 'messages' => []], $deployer->deploy($this->settings()));
        self::assertSame(1, $calls);
    }

    public function testInstallationLoadsThroughRegistryAndUnchangedRevisionIsNotDownloadedAgain(): void
    {
        $deployer = new ExtensionDeployer($this->root, $this->runCommand(...));
        self::assertTrue($deployer->deploy($this->settings())['changed']);
        self::assertSame('npm', $this->commands[5][0]);
        self::assertContains('--ignore-scripts', $this->commands[5]);
        self::assertContains($this->revision, $this->commands[2]);
        $config = new Config([]);
        $config->set('extensions', $this->settings());
        $registry = new ExtensionRegistry($config, $this->root);
        self::assertSame('demo', $registry->get('demo')['id']);
        self::assertFalse($deployer->deploy($this->settings())['changed']);
        self::assertCount(6, $this->commands);
        self::assertFalse($deployer->deploy($this->settings(), true)['changed']);
        self::assertCount(7, $this->commands);
    }

    public function testFailedUpdateKeepsExistingReleaseAndRemovesIncompleteDownload(): void
    {
        $deployer = new ExtensionDeployer($this->root, $this->runCommand(...));
        self::assertTrue($deployer->deploy($this->settings())['changed']);
        $current = realpath($this->root . '/var/extensions/demo/current');
        $this->revision = str_repeat('b', 40);
        $failing = new ExtensionDeployer($this->root, function (array $command, string $directory): string {
            if ($command[0] === 'npm') {
                throw new RuntimeException('Dependency installation failed');
            }
            return $this->runCommand($command, $directory);
        });
        self::assertFalse($failing->deploy($this->settings(), true)['changed']);
        clearstatcache(true);
        self::assertSame($current, realpath($this->root . '/var/extensions/demo/current'));
        self::assertCount(1, glob($this->root . '/var/extensions/demo/release-*'));
    }

    public function testChangedRevisionReplacesSymlinkAndOptionalRestartUsesArguments(): void
    {
        $deployer = new ExtensionDeployer($this->root, $this->runCommand(...));
        self::assertTrue($deployer->deploy($this->settings())['changed']);
        $previous = readlink($this->root . '/var/extensions/demo/current');
        $this->revision = str_repeat('b', 40);
        self::assertTrue($deployer->deploy($this->settings(), true)['changed']);
        self::assertNotSame($previous, readlink($this->root . '/var/extensions/demo/current'));
        $settings = $this->settings();
        $settings['demo']['deployment']['restartCommand'] = ['systemctl', '--user', 'try-restart', 'demo.service'];
        self::assertSame(['failed' => false, 'messages' => ['[extensions] demo restarted']], $deployer->restart($settings));
        self::assertSame($settings['demo']['deployment']['restartCommand'], $this->commands[array_key_last($this->commands)]);
    }

    public function testLocalCheckoutCannotBeOverwritten(): void
    {
        $settings = $this->settings();
        $settings['demo']['path'] = '../local';
        $deployer = new ExtensionDeployer($this->root, $this->runCommand(...));
        self::assertFalse($deployer->deploy($settings)['changed']);
        self::assertSame([], $this->commands);
    }

    public function testConfiguredBranchControlsUpdatesIndependentlyOfCore(): void
    {
        $deployer = new ExtensionDeployer($this->root, $this->runCommand(...));
        $settings = $this->settings();
        self::assertTrue($deployer->deploy($settings)['changed']);
        self::assertSame('refs/heads/dev', $this->commands[0][4]);

        $settings['demo']['deployment']['branch'] = 'master';
        self::assertFalse($deployer->deploy($settings, true)['changed']);
        self::assertSame('refs/heads/master', $this->commands[6][4]);
        self::assertCount(7, $this->commands);

        $this->revision = str_repeat('b', 40);
        self::assertTrue($deployer->deploy($settings, true)['changed']);
        self::assertSame('refs/heads/master', $this->commands[7][4]);
        self::assertContains($this->revision, $this->commands[9]);
    }

    public function testMissingBranchIsNotReplacedWithADefault(): void
    {
        $settings = $this->settings();
        unset($settings['demo']['deployment']['branch']);
        $deployer = new ExtensionDeployer($this->root, $this->runCommand(...));
        $result = $deployer->deploy($settings);
        self::assertFalse($result['changed']);
        self::assertStringContainsString('Invalid deployment branch', $result['messages'][0]);
        self::assertSame([], $this->commands);
    }

    private function settings(): array
    {
        return ['demo' => ['enabled' => true, 'deployment' => [
            'repository' => 'git@github.com:example/demo.git',
            'branch' => 'dev',
        ]]];
    }

    private function runCommand(array $command, string $directory): string
    {
        $this->commands[] = $command;
        if (($command[1] ?? null) === 'ls-remote') {
            return $this->revision . "\t" . $command[array_key_last($command)];
        }
        if (in_array('init', $command, true)) {
            $release = $command[array_key_last($command)];
            mkdir($release . '/server', 0750, true);
        }
        if (in_array('checkout', $command, true)) {
            $release = $directory;
            file_put_contents($release . '/module.php', '<?php return ' . var_export([
                'id' => 'demo', 'apiVersion' => 1, 'realtime' => ['entry' => 'server/server.js'],
            ], true) . ';');
            file_put_contents($release . '/server/server.js', '');
            file_put_contents($release . '/server/package-lock.json', '{}');
        }
        return ($command[1] ?? null) === 'rev-parse' ? $this->revision : '';
    }

    public function testExpiredIntervalFetchesNewBranchRevision(): void
    {
        $deployer = new ExtensionDeployer($this->root, $this->runCommand(...));
        self::assertTrue($deployer->deploy($this->settings())['changed']);
        $this->revision = str_repeat('b', 40);
        self::assertFalse($deployer->deploy($this->settings())['changed']);
        file_put_contents($this->root . '/var/extensions/demo/last-check', (string) (time() - 301));
        self::assertTrue($deployer->deploy($this->settings())['changed']);
        self::assertContains($this->revision, $this->commands[8]);
    }

    private function removeDirectory(string $directory): void
    {
        foreach (scandir($directory) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $directory . '/' . $entry;
            if (is_dir($path) && !is_link($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($directory);
    }
}
