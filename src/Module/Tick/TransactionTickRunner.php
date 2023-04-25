<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Throwable;

final class TransactionTickRunner implements TransactionTickRunnerInterface
{
    protected EntityManagerInterface $entityManager;
    protected FailureEmailSenderInterface $failureEmailSender;

    private GameControllerInterface $game;

    public function __construct(
        GameControllerInterface $game,
        EntityManagerInterface $entityManager,
        FailureEmailSenderInterface $failureEmailSender
    ) {
        $this->game = $game;
        $this->entityManager = $entityManager;
        $this->failureEmailSender = $failureEmailSender;
    }

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

            $this->failureEmailSender->sendMail(
                sprintf('stu %s failure', $tickDescription),
                sprintf(
                    "Current system time: %s\nThe %s cron caused an error:\n\n%s\n\n%s",
                    date('Y-m-d H:i:s'),
                    $tickDescription,
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );

            throw $e;
        }
    }

    public function isGameStateReset(): bool
    {
        return $this->game->getGameState() === GameEnum::CONFIG_GAMESTATE_VALUE_RESET;
    }
}
