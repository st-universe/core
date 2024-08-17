<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Throwable;

final class TransactionTickRunner implements TransactionTickRunnerInterface
{
    private LoggerUtilInterface $logger;

    public function __construct(
        private FailureEmailSenderInterface $failureEmailSender,
        private GameControllerInterface $game,
        private EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getLoggerUtil(true);
    }

    #[Override]
    public function runWithResetCheck(callable $fn, string $tickDescription, int $batchGroup, int $batchGroupCount): void
    {
        if (!$this->isGameStateReset()) {
            $this->runInTransaction(
                $fn,
                $tickDescription,
                $batchGroup,
                $batchGroupCount
            );
        }
    }

    private function runInTransaction(callable $fn, string $tickDescription, int $batchGroup, int $batchGroupCount): void
    {
        $this->entityManager->beginTransaction();

        try {
            $fn($batchGroup, $batchGroupCount);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();

            $subject = sprintf('stu %s failure', $tickDescription);
            $message = sprintf(
                "Current system time: %s\nThe %s cron caused an error:\n\n%s\n\n%s",
                date('Y-m-d H:i:s'),
                $tickDescription,
                $e->getMessage(),
                $e->getTraceAsString()
            );

            $this->failureEmailSender->sendMail(
                $subject,
                $message
            );

            $this->logger->logf(
                '%s, %s',
                $subject,
                $message
            );

            throw $e;
        }
    }

    #[Override]
    public function isGameStateReset(): bool
    {
        return $this->game->getGameState() === GameEnum::CONFIG_GAMESTATE_VALUE_RESET;
    }
}
