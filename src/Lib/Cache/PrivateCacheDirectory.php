<?php

declare(strict_types=1);

namespace Stu\Lib\Cache;

use RuntimeException;
use SplFileObject;

final class PrivateCacheDirectory
{
    private string $directory;
    private int $owner;

    public function __construct(?string $directory = null)
    {
        $probe = tmpfile();
        if ($probe === false) {
            throw new RuntimeException('Privater Cache kann nicht initialisiert werden');
        }
        $stat = fstat($probe);
        fclose($probe);
        if ($stat === false) {
            throw new RuntimeException('Cachebesitzer kann nicht bestimmt werden');
        }
        $this->owner = $stat['uid'];
        $this->directory = $directory ?? sprintf('%s/stu-private-%d-%s', sys_get_temp_dir(), $this->owner, substr(hash('sha256', dirname(__DIR__, 3)), 0, 16));
        $this->ensureDirectory();
    }

    public function path(string $name): string
    {
        if (!preg_match('/^[a-zA-Z0-9_.-]+$/D', $name) || $name === '.' || $name === '..') {
            throw new RuntimeException('Ungültiger Cachename');
        }
        $this->ensureDirectory();
        $path = $this->directory . '/' . $name;
        $this->validateFile($path);
        return $path;
    }

    public function open(string $name): SplFileObject
    {
        $path = $this->path($name);
        clearstatcache(true, $path);
        if (@lstat($path) === false) {
            try {
                $file = new SplFileObject($path, 'x+b');
                if (!chmod($path, 0o600)) {
                    throw new RuntimeException('Cacheberechtigungen können nicht gesetzt werden');
                }
            } catch (RuntimeException $error) {
                if (!file_exists($path)) {
                    throw $error;
                }
                $this->validateFile($path);
                $file = new SplFileObject($path, 'r+b');
            }
        } else {
            $file = new SplFileObject($path, 'r+b');
        }
        $this->validateFile($path);
        $opened = $file->fstat();
        $named = lstat($path);
        if ($named === false || $opened['ino'] !== $named['ino'] || $opened['dev'] !== $named['dev']) {
            throw new RuntimeException('Cachedatei wurde während des Öffnens verändert');
        }
        return $file;
    }

    private function ensureDirectory(): void
    {
        clearstatcache(true, $this->directory);
        if (@lstat($this->directory) === false) {
            @mkdir($this->directory, 0o700);
            clearstatcache(true, $this->directory);
        }
        $stat = @lstat($this->directory);
        if ($stat === false || ($stat['mode'] & 0o170000) !== 0o040000 || $stat['uid'] !== $this->owner || ($stat['mode'] & 0o077) !== 0) {
            throw new RuntimeException('Unsicheres privates Cacheverzeichnis');
        }
    }

    private function validateFile(string $path): void
    {
        clearstatcache(true, $path);
        $stat = @lstat($path);
        if ($stat === false) {
            return;
        }
        if (($stat['mode'] & 0o170000) !== 0o100000 || $stat['uid'] !== $this->owner || $stat['nlink'] !== 1 || ($stat['mode'] & 0o077) !== 0) {
            throw new RuntimeException('Unsichere Cachedatei');
        }
    }
}
