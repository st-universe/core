<?php

namespace Stu\Module\Tick;

use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\GameTurnStatsRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\UserLockRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class TickManager implements TickManagerInterface
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private GameTurnRepositoryInterface $gameTurnRepository,
        private UserLockRepositoryInterface $userLockRepository,
        private GameTurnStatsRepositoryInterface $gameTurnStatsRepository,
        private UserRepositoryInterface $userRepository,
        private KnPostRepositoryInterface $knPostRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function work(): void
    {
        $turn = $this->gameTurnRepository->getCurrent();
        $this->endTurn($turn);
        $this->reduceUserLocks();
        $newTurn = $this->startTurn($turn);
        $this->createGameTurnStats($newTurn);
    }

    private function endTurn(GameTurnInterface $turn): void
    {
        $turn->setEnd(time());

        $this->gameTurnRepository->save($turn);
    }

    private function startTurn(GameTurnInterface $turn): GameTurnInterface
    {
        $obj = $this->gameTurnRepository->prototype();
        $obj->setStart(time());
        $obj->setEnd(0);
        $obj->setTurn($turn->getTurn() + 1);

        $this->gameTurnRepository->save($obj);

        return $obj;
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
                $lock->setRemainingTicks(0);
            } else {
                $lock->setRemainingTicks($remainingTicks - 1);
            }

            $this->userLockRepository->save($lock);
        }
    }

    private function createGameTurnStats(GameTurnInterface $turn): void
    {
        $stats = $this->gameTurnStatsRepository->prototype();

        $this->loggerUtil->log('setting stats values');

        $stats->setTurn($turn);
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

        $this->gameTurnStatsRepository->save($stats);
        $this->loggerUtil->log('saved stats');
    }
}
