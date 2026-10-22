<?php

declare(strict_types=1);

namespace Stu\Lib\Request;

use RuntimeException;
use SplFileObject;
use stdClass;
use Stu\Lib\Cache\PrivateCacheDirectory;
use Stu\Module\Control\StuTime;

class RequestRateLimiter
{
    private const int MAX_BUCKETS = 4096;

    public function __construct(private PrivateCacheDirectory $cache, private StuTime $time) {}

    public function source(): string
    {
        $address = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $binary = @inet_pton($address);
        return $binary === false ? 'unknown' : bin2hex($binary);
    }

    public function consume(string $scope, string $identifier, int $limit, int $window): void
    {
        $file = $this->cache->open('request-rates.json');
        if (!$file->flock(LOCK_EX)) {
            throw new RuntimeException('Anfragelimit kann nicht gesperrt werden');
        }
        try {
            $now = $this->time->time();
            $state = $this->read($file);
            foreach (get_object_vars($state) as $key => $entry) {
                if (!$entry instanceof stdClass || !is_int($entry->expires ?? null) || $entry->expires <= $now) {
                    unset($state->$key);
                }
            }
            $key = hash('sha256', $scope . ':' . $identifier);
            $entry = $state->$key ?? null;
            $attempts = $entry instanceof stdClass && is_array($entry->attempts ?? null) ? $entry->attempts : [];
            $attempts = array_values(array_filter($attempts, static fn (mixed $value): bool => is_int($value) && $value > $now - $window));
            if (count($attempts) >= $limit) {
                throw new RequestLimitException(max(1, (int) $attempts[0] + $window - $now));
            }
            if ($entry === null && count(get_object_vars($state)) >= self::MAX_BUCKETS) {
                throw new RequestLimitException(60);
            }
            $attempts[] = $now;
            $state->$key = (object) ['expires' => $now + $window, 'attempts' => $attempts];
            $this->write($file, $state);
        } finally {
            $file->flock(LOCK_UN);
        }
    }

    public function reset(string $scope, string $identifier): void
    {
        $file = $this->cache->open('request-rates.json');
        if (!$file->flock(LOCK_EX)) {
            throw new RuntimeException('Anfragelimit kann nicht gesperrt werden');
        }
        try {
            $state = $this->read($file);
            $key = hash('sha256', $scope . ':' . $identifier);
            unset($state->$key);
            $this->write($file, $state);
        } finally {
            $file->flock(LOCK_UN);
        }
    }

    public function concurrent(string $scope, int $limit, callable $callback): mixed
    {
        for ($index = 0; $index < $limit; $index++) {
            $slot = $this->cache->open('request-slot-' . hash('sha256', $scope) . '-' . $index . '.lock');
            if (!$slot->flock(LOCK_EX | LOCK_NB)) {
                continue;
            }
            try {
                return $callback();
            } finally {
                $slot->flock(LOCK_UN);
            }
        }
        throw new RequestLimitException(1);
    }

    private function read(SplFileObject $file): stdClass
    {
        $file->rewind();
        $contents = $file->fread(max(1, $file->fstat()['size']));
        if ($contents === false) {
            throw new RuntimeException('Anfragelimit kann nicht gelesen werden');
        }
        if ($contents === '') {
            return new stdClass();
        }
        $state = json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
        if (!$state instanceof stdClass) {
            throw new RuntimeException('Ungültiger Anfragelimitzustand');
        }
        return $state;
    }

    private function write(SplFileObject $file, stdClass $state): void
    {
        $contents = json_encode($state, JSON_THROW_ON_ERROR);
        $file->rewind();
        if (!$file->ftruncate(0) || $file->fwrite($contents) !== strlen($contents) || !$file->fflush()) {
            throw new RuntimeException('Anfragelimit kann nicht gespeichert werden');
        }
    }
}
