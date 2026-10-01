<?php

declare(strict_types=1);

namespace Stu\Extension;

use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExtensionProcessTest extends TestCase
{
    public function testRunsWithoutInteractiveGitAuthentication(): void
    {
        $output = new ExtensionProcess()([PHP_BINARY, '-r', 'echo getenv("GIT_TERMINAL_PROMPT") . "|" . getenv("GIT_SSH_COMMAND");'], sys_get_temp_dir());
        self::assertStringStartsWith('0|', $output);
        self::assertStringContainsString('BatchMode=yes', $output);
        self::assertStringContainsString('StrictHostKeyChecking=yes', $output);
    }

    public function testCommandErrorsDoNotExposeCredentialOutput(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Deployment command failed; check access, branch and dependencies');
        new ExtensionProcess()([PHP_BINARY, '-r', 'fwrite(STDERR, "secret-token"); exit(1);'], sys_get_temp_dir());
    }
}
