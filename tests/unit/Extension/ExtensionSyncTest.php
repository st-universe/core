<?php

declare(strict_types=1);

namespace Stu\Extension;

use Stu\StuTestCase;

final class ExtensionSyncTest extends StuTestCase
{
    private string $root;

    #[\Override]
    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/stu-sync-test-' . bin2hex(random_bytes(8));
        mkdir($this->root, 0750, true);

        $shell = @proc_open(['sh', '-c', 'exit 0'], [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);
        if (!is_resource($shell)) {
            self::markTestSkipped('The sync integration test requires sh.');
        }
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        proc_close($shell);

        mkdir($this->root . '/commands', 0750, true);
        mkdir($this->root . '/config');
        mkdir($this->root . '/.git');
        copy(dirname(__DIR__, 3) . '/sync.sh', $this->root . '/sync.sh');
        file_put_contents($this->root . '/config/config.json', '{}');
        file_put_contents($this->root . '/syncFailure.mail', 'test');
        $script = <<<'SH'
#!/bin/sh
command=$(basename "$0")
printf '%s %s\n' "$command" "$*" >> "$PWD/trace"
case "$command" in
  git)
    if [ "$1" = rev-parse ]; then
      case "$2" in
        --git-path) echo '.git/stu-sync.lock' ;;
        @) echo 'same' ;;
        *) echo "${FAKE_REMOTE:-same}" ;;
      esac
    fi
    ;;
  php)
    if [ "$1" = bin/deploy-extensions.php ] && [ "$2" != --restart ]; then
      exit "${FAKE_EXTENSION_RESULT:-0}"
    fi
    ;;
  make)
    if [ "$1" = migrateDatabase ] && [ -f fail-migration ]; then
      exit 1
    fi
    ;;
  jq) echo '{}' ;;
  sponge) cat > "$1" ;;
  sendmail) cat > /dev/null ;;
esac
SH;
        foreach (['git', 'php', 'make', 'jq', 'sponge', 'sendmail'] as $command) {
            file_put_contents($this->root . '/commands/' . $command, $script);
            chmod($this->root . '/commands/' . $command, 0750);
        }
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($this->root);
    }

    public function testUnchangedCoreChecksModulesWithoutMigratingOnSkip(): void
    {
        self::assertSame(0, $this->runSync(0));
        $trace = file_get_contents($this->root . '/trace');
        self::assertStringContainsString('php bin/deploy-extensions.php', $trace);
        self::assertStringNotContainsString('make ', $trace);
        self::assertStringNotContainsString('git pull', $trace);
    }

    public function testCoreUpdateDeploysModulesAndKeepsOriginalGitWorkflow(): void
    {
        self::assertSame(0, $this->runSync(10, true));
        $trace = file_get_contents($this->root . '/trace');
        self::assertStringContainsString('make migrateDatabase', $trace);
        self::assertStringContainsString('php bin/publish-extension-assets.php', $trace);
        self::assertStringContainsString('php bin/deploy-extensions.php --restart', $trace);
        self::assertStringContainsString('make init-production', $trace);
        self::assertStringContainsString('git reset --hard HEAD', $trace);
        self::assertStringContainsString('git pull --rebase', $trace);
        self::assertFileDoesNotExist($this->root . '/var/extensions/.pending-deploy');
    }

    public function testFailedFinalizationIsRetriedEvenWithoutAnotherDownload(): void
    {
        touch($this->root . '/fail-migration');
        self::assertSame(1, $this->runSync(10, true));
        self::assertFileExists($this->root . '/var/extensions/.pending-deploy');
        self::assertStringNotContainsString('--restart', file_get_contents($this->root . '/trace'));
        unlink($this->root . '/fail-migration');
        self::assertSame(0, $this->runSync(0));
        self::assertFileDoesNotExist($this->root . '/var/extensions/.pending-deploy');
    }

    public function testModuleOnlyUpdateFinalizesDeploymentAndRestarts(): void
    {
        self::assertSame(0, $this->runSync(10));
        $trace = file_get_contents($this->root . '/trace');
        self::assertStringContainsString('make migrateDatabase', $trace);
        self::assertStringContainsString('php bin/publish-extension-assets.php', $trace);
        self::assertStringContainsString('php bin/deploy-extensions.php --restart', $trace);
        self::assertStringNotContainsString('make init-production', $trace);
        self::assertStringNotContainsString('git pull', $trace);
        self::assertFileDoesNotExist($this->root . '/var/extensions/.pending-deploy');
    }

    private function runSync(int $extensionResult, bool $coreChanged = false): int
    {
        $environment = getenv();
        $environment['PATH'] = $this->root . '/commands:' . $environment['PATH'];
        $environment['FAKE_EXTENSION_RESULT'] = (string) $extensionResult;
        $environment['FAKE_REMOTE'] = $coreChanged ? 'new' : 'same';
        $process = proc_open(['sh', $this->root . '/sync.sh'], [
            0 => ['file', '/dev/null', 'r'],
            1 => ['file', $this->root . '/output', 'a'],
            2 => ['file', $this->root . '/output', 'a'],
        ], $pipes, $this->root, $environment);
        self::assertIsResource($process);
        return proc_close($process);
    }
}
