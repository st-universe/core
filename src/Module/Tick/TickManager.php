<?php

namespace Stu\Module\Tick;

use Stu\Module\Tick\Colony\ColonyTickManager;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\UserLockRepositoryInterface;

final class TickManager implements TickManagerInterface
{
    public const PROCESS_COUNT = 1;

    private GameTurnRepositoryInterface $gameTurnRepository;

    private UserLockRepositoryInterface $userLockRepository;

    public function __construct(
        GameTurnRepositoryInterface $gameTurnRepository,
        UserLockRepositoryInterface $userLockRepository
    ) {
        $this->gameTurnRepository = $gameTurnRepository;
        $this->userLockRepository = $userLockRepository;
    }

    public function work(): void
    {
        $turn = $this->gameTurnRepository->getCurrent();
        $this->endTurn($turn);
        $this->mainLoop();
        $this->reduceUserLocks();
        $this->startTurn($turn);
    }

    private function endTurn(GameTurnInterface $turn): void
    {
        $turn->setEnd(time());

        $this->gameTurnRepository->save($turn);
    }

    private function startTurn(GameTurnInterface $turn): void
    {
        $obj = $this->gameTurnRepository->prototype();
        $obj->setStart(time());
        $obj->setEnd(0);
        $obj->setTurn($turn->getTurn() + 1);

        $this->gameTurnRepository->save($obj);
    }

    private function mainLoop(): void
    {
        while (true) {
            if ($this->hitLockFiles() === false) {
                break;
            }
            sleep(1);
        }
    }

    private function reduceUserLocks(): void
    {
        $locks = $this->userLockRepository->getActive();

        foreach ($locks as $lock) {
            $remainingTicks = $lock->getRemainingTicks();

            if ($remainingTicks === 1) {
                $userId = $lock->getUser()->getId();

                $lock->setUser(null);
                $lock->setUserId(null);
                $lock->setFormerUserId($userId);
            } else {
                $lock->setRemainingTicks($remainingTicks - 1);
            }

            $this->userLockRepository->save($lock);
        }
    }

    private function hitLockFiles(): bool
    {
        for ($i = 1; $i <= self::PROCESS_COUNT; $i++) {
            if (@file_exists(ColonyTickManager::LOCKFILE_DIR . 'col' . $i . '.lock')) {
                return true;
            }
        }
        return false;
    }
}
