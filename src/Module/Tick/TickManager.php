<?php

namespace Stu\Module\Tick;

use BadMethodCallException;
use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Module\Logging\StuLogger;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\GameTurnStatsRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserLockRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class TickManager implements TickManagerInterface
{
    public function __construct(
        private readonly GameTurnRepositoryInterface $gameTurnRepository,
        private readonly UserLockRepositoryInterface $userLockRepository,
        private readonly GameTurnStatsRepositoryInterface $gameTurnStatsRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly KnPostRepositoryInterface $knPostRepository,
        private readonly PrivateMessageRepositoryInterface $privateMessageRepository
    ) {}

    #[Override]
    public function work(): void
    {
        $oldTurn = $this->gameTurnRepository->getCurrent();
        if ($oldTurn === null) {
            throw new BadMethodCallException('no current turn existent');
        }

        $this->endTurn($oldTurn);
        $this->reduceUserLocks();
        $newTurn = $this->startTurn($oldTurn);
        $this->createGameTurnStats($oldTurn, $newTurn);
    }

    private function endTurn(GameTurn $turn): void
    {
        $turn->setEnd(time());

        $this->gameTurnRepository->save($turn);
    }

    private function startTurn(GameTurn $oldTurn): GameTurn
    {
        $obj = $this->gameTurnRepository->prototype();
        $obj->setStart(time());
        $obj->setEnd(0);
        $obj->setTurn($oldTurn->getTurn() + 1);

        $this->gameTurnRepository->save($obj);

        return $obj;
    }

    private function reduceUserLocks(): void
    {
        $locks = $this->userLockRepository->getActive();

        foreach ($locks as $lock) {
            $remainingTicks = $lock->getRemainingTicks();

            if ($remainingTicks === 1) {
                $userId = $lock->getUser()?->getId() ?? 0;

                $lock->setUser(null);
                $lock->setFormerUserId($userId);
                $lock->setRemainingTicks(0);
            } else {
                $lock->setRemainingTicks($remainingTicks - 1);
            }

            $this->userLockRepository->save($lock);
        }
    }

    private function createGameTurnStats(GameTurn $oldTurn, GameTurn $newTurn): void
    {
        $stats = $this->gameTurnStatsRepository->prototype();

        StuLogger::log('setting stats values');

        $stats->setTurn($newTurn);
        $stats->setUserCount($this->userRepository->getActiveAmount());
        $stats->setLogins24h($this->userRepository->getActiveAmountRecentlyOnline(time() - TimeConstants::ONE_DAY_IN_SECONDS));
        $stats->setInactiveCount($this->userRepository->getInactiveAmount(14));
        $stats->setVacationCount($this->userRepository->getVacationAmount());
        $stats->setShipCount($this->gameTurnStatsRepository->getShipCount());
        $stats->setShipCountManned($this->gameTurnStatsRepository->getShipCountManned());
        $stats->setShipCountNpc($this->gameTurnStatsRepository->getShipCountNpc());
        $stats->setKnCount($this->knPostRepository->getAmount());
        $stats->setFlightSig24h($this->gameTurnStatsRepository->getFlightSigs24h());
        $stats->setFlightSigSystem24h($this->gameTurnStatsRepository->getFlightSigsSystem24h());
        $stats->setNewPmCount($this->privateMessageRepository->getAmountSince($oldTurn->getStart()));

        $this->gameTurnStatsRepository->save($stats);
        StuLogger::log('saved stats');
    }
}
