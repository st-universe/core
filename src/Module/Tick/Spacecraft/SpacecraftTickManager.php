<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Spacecraft;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\SemaphoreUtilInterface;
use Stu\Module\Logging\LogTypeEnum;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Module\Tick\Spacecraft\ManagerComponent\ManagerComponentInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Throwable;

class SpacecraftTickManager implements SpacecraftTickManagerInterface
{
    /** @param array<ManagerComponentInterface> $components */
    public function __construct(
        private SemaphoreUtilInterface $semaphoreUtil,
        private UserRepositoryInterface $userRepository,
        private LockManagerInterface $lockManager,
        private StuConfigInterface $config,
        private EntityManagerInterface $entityManager,
        private array $components
    ) {}

    #[\Override]
    public function work(bool $doCommit = false): void
    {
        $mainSema = $this->semaphoreUtil->acquireSemaphore(SemaphoreConstants::MAIN_SHIP_SEMAPHORE_KEY);
        $lockSet = false;

        try {
            $this->setLock(1);
            $lockSet = true;
            $this->lockAllUsersForUpdate();

            foreach ($this->components as $component) {
                $startTime = microtime(true);
                $component->work();

                $endTime = microtime(true);
                StuLogger::log(sprintf("\t\t%s, seconds: %F", get_class($component), $endTime - $startTime), LogTypeEnum::TICK);
            }

            if ($doCommit) {
                $this->entityManager->flush();
                $this->entityManager->commit();
            }
        } catch (Throwable $e) {
            $this->semaphoreUtil->releaseSemaphore($mainSema);

            throw $e;
        } finally {
            if ($lockSet) {
                $this->clearLock(1);
            }
        }
    }

    private function setLock(int $batchGroupId): void
    {
        $this->lockManager->setLock($batchGroupId, LockTypeEnum::SHIP_GROUP);
    }

    private function lockAllUsersForUpdate(): void
    {
        if (!$this->config->getDbSettings()->useSqlite()) {
            $this->userRepository->lockAllUsersForUpdate();
        }
    }

    private function clearLock(int $batchGroupId): void
    {
        $this->lockManager->clearLock($batchGroupId, LockTypeEnum::SHIP_GROUP);
    }
}
