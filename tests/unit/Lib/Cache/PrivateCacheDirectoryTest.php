<?php

declare(strict_types=1);

namespace Stu\Lib\Cache;

use RuntimeException;
use Stu\StuTestCase;

final class PrivateCacheDirectoryTest extends StuTestCase
{
    private string $directory;
    private string $sandbox;

    protected function setUp(): void
    {
        $this->sandbox = sys_get_temp_dir() . '/stu-private-sandbox-' . bin2hex(random_bytes(8));
        mkdir($this->sandbox, 0o700);
        $this->directory = $this->sandbox . '/cache';
    }

    protected function tearDown(): void
    {
        foreach ([$this->directory, $this->sandbox . '/target'] as $directory) {
            if (is_link($directory)) {
                unlink($directory);
            } elseif (is_dir($directory)) {
                foreach (new \DirectoryIterator($directory) as $entry) {
                    if (!$entry->isDot()) {
                        unlink($entry->getPathname());
                    }
                }
                rmdir($directory);
            }
        }
        rmdir($this->sandbox);
        parent::tearDown();
    }

    public function testNormalFilesArePrivateAndPreserveDataAcrossWorkers(): void
    {
        $cache = new PrivateCacheDirectory($this->directory);
        $file = $cache->open('normal.json');
        $file->fwrite('test');
        $file->fflush();
        self::assertSame(0o700, fileperms($this->directory) & 0o777);
        self::assertSame(0o600, $file->fstat()['mode'] & 0o777);
        $second = new PrivateCacheDirectory($this->directory)->open('normal.json');
        self::assertSame('test', $second->fread(4));
    }

    public function testPrecreatedDirectorySymlinkIsRejected(): void
    {
        $target = $this->sandbox . '/target';
        mkdir($target, 0o700);
        file_put_contents($target . '/keep', 'testdata');
        symlink($target, $this->directory);
        $this->expectException(RuntimeException::class);
        new PrivateCacheDirectory($this->directory);
    }

    public function testSharedReadableDirectoryIsRejected(): void
    {
        mkdir($this->directory, 0o700);
        chmod($this->directory, 0o755);
        $this->expectException(RuntimeException::class);
        new PrivateCacheDirectory($this->directory);
    }

    public function testPrecreatedCacheSymlinkCannotOverwriteItsTarget(): void
    {
        $cache = new PrivateCacheDirectory($this->directory);
        $target = $cache->open('target');
        $target->fwrite('secret');
        $target->fflush();
        symlink($target->getPathname(), $this->directory . '/evil.lock');
        try {
            $cache->open('evil.lock');
            self::fail('Symlink akzeptiert');
        } catch (RuntimeException) {
            $target->rewind();
            self::assertSame('secret', $target->fread(6));
        }
    }

    public function testHardlinksAndSharedReadableFilesAreRejected(): void
    {
        $cache = new PrivateCacheDirectory($this->directory);
        $cache->open('target');
        link($this->directory . '/target', $this->directory . '/linked');
        try {
            $cache->open('linked');
            self::fail('Hardlink akzeptiert');
        } catch (RuntimeException) {
            self::assertSame(2, lstat($this->directory . '/linked')['nlink']);
        }
        unlink($this->directory . '/linked');
        chmod($this->directory . '/target', 0o644);
        $this->expectException(RuntimeException::class);
        $cache->open('target');
    }
}
