<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\User;

final class BorgAssimilation
{
    public const int DURATION_MILLISECONDS = 150_000;

    private const string STORAGE_KEY = 'anniversary_25_borg_started_at';
    private const string DISABLED_KEY = 'anniversary_25_borg_disabled';

    private int $startedAt = 0;

    public function __construct(
        private readonly SessionStorageInterface $sessionStorage,
        private readonly StuTime $stuTime
    ) {}

    public function getElapsedMilliseconds(?User $user): int
    {
        if (!$this->isEnabled($user)) {
            unset($_SESSION[self::STORAGE_KEY]);
            $this->startedAt = 0;
            return 0;
        }

        $now = $this->stuTime->time();
        $guestStart = $_SESSION[self::STORAGE_KEY] ?? $now;
        if (!is_int($guestStart) || $guestStart < 0 || $guestStart > $now) {
            $guestStart = $now;
        }

        if ($user === null) {
            $_SESSION[self::STORAGE_KEY] = $guestStart;
            $startedAt = $guestStart;
        } else {
            $startedAt = $this->getAccountStartedAt($guestStart, $now);
        }

        $this->startedAt = $startedAt;

        return min(self::DURATION_MILLISECONDS, ($now - $startedAt) * 1000);
    }

    public function getStartedAt(): int
    {
        return $this->startedAt;
    }

    public function isEnabled(?User $user): bool
    {
        return $user === null || $this->sessionStorage->getSessionValue(self::DISABLED_KEY) !== true;
    }

    public function deassimilate(User $user): void
    {
        unset($_SESSION[self::STORAGE_KEY]);
        $this->sessionStorage->deleteSessionData(self::STORAGE_KEY);
        $this->sessionStorage->deleteSessionData(self::DISABLED_KEY);
        $this->sessionStorage->storeSessionData(self::DISABLED_KEY, true, true);
        $this->startedAt = 0;
    }

    public function reassimilate(User $user): void
    {
        $this->sessionStorage->deleteSessionData(self::DISABLED_KEY);
        $this->restart($user);
    }

    public function restart(?User $user): void
    {
        $this->startedAt = $this->stuTime->time();
        if ($user === null) {
            $_SESSION[self::STORAGE_KEY] = $this->startedAt;
            return;
        }

        unset($_SESSION[self::STORAGE_KEY]);
        $this->sessionStorage->deleteSessionData(self::STORAGE_KEY);
        $this->sessionStorage->storeSessionData(self::STORAGE_KEY, $this->startedAt, true);
    }

    private function getAccountStartedAt(int $guestStart, int $now): int
    {
        $storedStart = $this->sessionStorage->getSessionValue(self::STORAGE_KEY);
        $startedAt = is_int($storedStart) && $storedStart >= 0 && $storedStart <= $now
            ? min($storedStart, $guestStart)
            : $guestStart;
        if ($storedStart !== $startedAt) {
            if ($storedStart !== false) {
                $this->sessionStorage->deleteSessionData(self::STORAGE_KEY);
            }
            $this->sessionStorage->storeSessionData(self::STORAGE_KEY, $startedAt, true);
        }
        unset($_SESSION[self::STORAGE_KEY]);

        return $startedAt;
    }
}
