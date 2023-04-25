<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Trade;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\BasicTradeRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\LotteryTicketRepositoryInterface;
use Stu\Orm\Repository\TradeShoutboxRepositoryInterface;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;

final class TradeReset implements TradeResetInterface
{
    private BasicTradeRepositoryInterface $basicTradeRepository;

    private DealsRepositoryInterface $dealsRepository;

    private LotteryTicketRepositoryInterface $lotteryTicketRepository;

    private TradeShoutboxRepositoryInterface $tradeShoutboxRepository;

    private TradeTransactionRepositoryInterface $tradeTransactionRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        BasicTradeRepositoryInterface $basicTradeRepository,
        DealsRepositoryInterface $dealsRepository,
        LotteryTicketRepositoryInterface $lotteryTicketRepository,
        TradeShoutboxRepositoryInterface $tradeShoutboxRepository,
        TradeTransactionRepositoryInterface $tradeTransactionRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->basicTradeRepository = $basicTradeRepository;
        $this->dealsRepository = $dealsRepository;
        $this->lotteryTicketRepository = $lotteryTicketRepository;
        $this->tradeShoutboxRepository = $tradeShoutboxRepository;
        $this->tradeTransactionRepository = $tradeTransactionRepository;
        $this->entityManager = $entityManager;
    }

    public function deleteAllBasicTrades(): void
    {
        echo "  - deleting all basic trades\n";

        $this->basicTradeRepository->truncateAllBasicTrades();

        $this->entityManager->flush();
    }

    public function deleteAllDeals(): void
    {
        echo "  - deleting all deals\n";

        $this->dealsRepository->truncateAllDeals();

        $this->entityManager->flush();
    }

    public function deleteAllLotteryTickets(): void
    {
        echo "  - deleting all lottery tickets\n";

        $this->lotteryTicketRepository->truncateAllLotteryTickets();

        $this->entityManager->flush();
    }

    public function deleteAllTradeShoutboxEntries(): void
    {
        echo "  - deleting all trade shoutbox entries\n";

        $this->tradeShoutboxRepository->truncateAllEntries();

        $this->entityManager->flush();
    }

    public function deleteAllTradeTransactions(): void
    {
        echo "  - deleting all trade transactions\n";

        $this->tradeTransactionRepository->truncateAllTradeTransactions();

        $this->entityManager->flush();
    }
}
