<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Spacecraft;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Module\Control\SemaphoreUtilInterface;
use Stu\Module\Logging\LogTypeEnum;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Module\Tick\Spacecraft\ManagerComponent\ManagerComponentInterface;

class SpacecraftTickManager implements SpacecraftTickManagerInterface
{
    /** @param array<ManagerComponentInterface> $components */
    public function __construct(
        private SemaphoreUtilInterface $semaphoreUtil,
        private LockManagerInterface $lockManager,
        private EntityManagerInterface $entityManager,
        private array $components
    ) {}

    #[Override]
    public function work(bool $doCommit = false): void
    {
        $this->setLock(1);

        try {
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
        } finally {
            $this->clearLock(1);
        }
    }

    private function setLock(int $batchGroupId): void
    {
        //main ship sema on
        $mainSema = $this->semaphoreUtil->acquireSemaphore(SemaphoreConstants::MAIN_SHIP_SEMAPHORE_KEY);

        $this->lockManager->setLock($batchGroupId, LockTypeEnum::SHIP_GROUP);

        //main ship sema off
        $this->semaphoreUtil->releaseSemaphore($mainSema);
    }

    private function clearLock(int $batchGroupId): void
    {
        $this->lockManager->clearLock($batchGroupId, LockTypeEnum::SHIP_GROUP);
    }
}
