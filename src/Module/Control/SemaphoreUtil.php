<?php

namespace Stu\Module\Control;

use Override;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Exception\SemaphoreException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LogTypeEnum;
use Stu\Module\Logging\StuLogger;
use SysvSemaphore;

final class SemaphoreUtil implements SemaphoreUtilInterface
{
    /** @var array<int, SysvSemaphore> */
    public static array $semaphores = [];

    public function __construct(private readonly StuConfigInterface $stuConfig) {}

    #[Override]
    public function isSemaphoreAlreadyAcquired(int $key): bool
    {
        return array_key_exists($key, self::$semaphores);
    }

    #[Override]
    public function acquireSemaphore(int $key): null|int|SysvSemaphore
    {
        if (!$this->isSemaphoreUsageActive()) {
            return null;
        }

        $semaphore = $this->getSemaphore($key);

        if ($this->isSemaphoreAlreadyAcquired($key)) {
            return null;
        }

        $this->acquire($semaphore);
        self::$semaphores[$key] = $semaphore;

        return $semaphore;
    }

    private function getSemaphore(int $key): SysvSemaphore
    {
        $semaphore = sem_get(
            $key,
            1,
            0o666,
            SemaphoreConstants::AUTO_RELEASE_SEMAPHORES
        );

        if ($semaphore === false) {
            throw new SemaphoreException('Error getting semaphore');
        }

        return $semaphore;
    }

    private function acquire(SysvSemaphore $semaphore): void
    {
        if (!sem_acquire($semaphore)) {
            throw new SemaphoreException("Error acquiring Semaphore!");
        }
    }

    #[Override]
    public function releaseSemaphore(null|int|SysvSemaphore $semaphore, bool $doRemove = false): void
    {
        if (!$this->isSemaphoreUsageActive() || !$semaphore instanceof SysvSemaphore) {
            return;
        }

        $this->release($semaphore, $doRemove);
    }

    #[Override]
    public function releaseAllSemaphores(bool $doRemove = false): void
    {
        if (!$this->isSemaphoreUsageActive()) {
            return;
        }

        foreach (self::$semaphores as $semaphore) {
            $this->release($semaphore, $doRemove);
        }
    }

    private function release(SysvSemaphore $semaphore, bool $doRemove): void
    {
        $key = array_search($semaphore, self::$semaphores, true);
        if ($key === false) {
            return;
        }
        unset(self::$semaphores[$key]);

        if (!sem_release($semaphore)) {
            StuLogger::log(sprintf("Error releasing Semaphore with key %d!", $key), LogTypeEnum::SEMAPHORE);
            return;
            //throw new SemaphoreException("Error releasing Semaphore!");
        } else {
            StuLogger::log(sprintf('  Released semaphore %d', $key), LogTypeEnum::SEMAPHORE);
        }

        if ($doRemove && !sem_remove($semaphore)) {
            StuLogger::log(sprintf("Error removing Semaphore with key %d!", $key), LogTypeEnum::SEMAPHORE);
            //throw new SemaphoreException("Error removing Semaphore!");
        }
    }

    private function isSemaphoreUsageActive(): bool
    {
        return $this->stuConfig->getGameSettings()->useSemaphores();
    }

    public static function reset(): void
    {
        self::$semaphores = [];
    }
}
