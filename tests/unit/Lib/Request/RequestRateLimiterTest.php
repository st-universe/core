<?php

declare(strict_types=1);

namespace Stu\Lib\Request;

use Stu\Lib\Cache\PrivateCacheDirectory;
use Stu\Module\Control\StuTime;
use Stu\StuTestCase;

final class RequestRateLimiterTest extends StuTestCase
{
    private string $directory;
    private StuTime $clock;
    private int $now = 1000;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/stu-limit-test-' . bin2hex(random_bytes(8));
        $this->clock = $this->mock(StuTime::class);
        $this->clock->shouldReceive('time')->andReturnUsing(fn () => $this->now);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') ?: [] as $path) {
            unlink($path);
        }
        if (is_dir($this->directory)) {
            rmdir($this->directory);
        }
        parent::tearDown();
    }

    private function limiter(): RequestRateLimiter
    {
        return new RequestRateLimiter(new PrivateCacheDirectory($this->directory), $this->clock);
    }

    public function testDistributedAndCrossWorkerAttemptsShareTheSameBoundedAccountWindow(): void
    {
        for ($index = 0; $index < 5; $index++) {
            $this->limiter()->consume('account', '101', 5, 60);
        }
        try {
            $this->limiter()->consume('account', '101', 5, 60);
            self::fail('Grenze wurde nicht durchgesetzt');
        } catch (RequestLimitException $error) {
            self::assertSame(60, $error->getRetryAfter());
        }
        $this->limiter()->consume('account', '102', 5, 60);
        $this->now += 61;
        $this->limiter()->consume('account', '101', 5, 60);
    }

    public function testSuccessResetsOnlyTheAccountAndKeepsTheSourceBudget(): void
    {
        $limiter = $this->limiter();
        $limiter->consume('source', 'ip', 1, 60);
        $limiter->consume('account', '101', 1, 60);
        $limiter->reset('account', '101');
        $limiter->consume('account', '101', 1, 60);
        $this->expectException(RequestLimitException::class);
        $limiter->consume('source', 'ip', 1, 60);
    }

    public function testPasswordVerificationSlotsAreBoundedAndReleasedAfterFailure(): void
    {
        $limiter = $this->limiter();
        $limiter->concurrent('verify', 1, function () use ($limiter): void {
            try {
                $limiter->concurrent('verify', 1, static fn () => self::fail('Parallele Prüfung wurde zugelassen'));
                self::fail('Keine Ablehnung');
            } catch (RequestLimitException $error) {
                self::assertSame(1, $error->getRetryAfter());
            }
        });
        self::assertSame('allowed', $limiter->concurrent('verify', 1, static fn () => 'allowed'));
    }

    public function testConcurrentProcessesCannotOverspendTheSameBudget(): void
    {
        $this->limiter();
        $children = [];
        for ($index = 0; $index < 8; $index++) {
            $pid = pcntl_fork();
            self::assertNotSame(-1, $pid);
            if ($pid === 0) {
                try {
                    $this->limiter()->consume('race', 'target', 3, 60);
                    exit(0);
                } catch (RequestLimitException) {
                    exit(10);
                }
            }
            $children[] = $pid;
        }
        $allowed = 0;
        $denied = 0;
        foreach ($children as $child) {
            pcntl_waitpid($child, $status);
            $exit = pcntl_wexitstatus($status);
            self::assertContains($exit, [0, 10]);
            $allowed += (int) ($exit === 0);
            $denied += (int) ($exit === 10);
        }
        self::assertSame(3, $allowed);
        self::assertSame(5, $denied);
    }

    public function testClientAddressIsCanonicalAndDoesNotTrustForwardedHeaders(): void
    {
        $previous = $_SERVER;
        try {
            $_SERVER['REMOTE_ADDR'] = '2001:db8::1';
            $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
            $first = $this->limiter()->source();
            $_SERVER['REMOTE_ADDR'] = '2001:0db8:0000:0000:0000:0000:0000:0001';
            self::assertSame($first, $this->limiter()->source());
            unset($_SERVER['REMOTE_ADDR']);
            self::assertSame('unknown', $this->limiter()->source());
        } finally {
            $_SERVER = $previous;
        }
    }
}
