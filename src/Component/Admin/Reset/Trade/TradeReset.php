<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Trade;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\BasicTradeRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\LotteryTicketRepositoryInterface;
use Stu\Orm\Repository\TradeShoutboxRepositoryInterface;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;

final class TradeReset implements TradeResetInterface
{
    public function __construct(private BasicTradeRepositoryInterface $basicTradeRepository, private DealsRepositoryInterface $dealsRepository, private LotteryTicketRepositoryInterface $lotteryTicketRepository, private TradeShoutboxRepositoryInterface $tradeShoutboxRepository, private TradeTransactionRepositoryInterface $tradeTransactionRepository, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function deleteAllBasicTrades(): void
    {
        echo "  - deleting all basic trades\n";

        $this->basicTradeRepository->truncateAllBasicTrades();

        $this->entityManager->flush();
    }

    #[Override]
    public function deleteAllDeals(): void
    {
        echo "  - deleting all deals\n";

        $this->dealsRepository->truncateAllDeals();

        $this->entityManager->flush();
    }

    #[Override]
    public function deleteAllLotteryTickets(): void
    {
        echo "  - deleting all lottery tickets\n";

        $this->lotteryTicketRepository->truncateAllLotteryTickets();

        $this->entityManager->flush();
    }

    #[Override]
    public function deleteAllTradeShoutboxEntries(): void
    {
        echo "  - deleting all trade shoutbox entries\n";

        $this->tradeShoutboxRepository->truncateAllEntries();

        $this->entityManager->flush();
    }

    #[Override]
    public function deleteAllTradeTransactions(): void
    {
        echo "  - deleting all trade transactions\n";

        $this->tradeTransactionRepository->truncateAllTradeTransactions();

        $this->entityManager->flush();
    }
}
