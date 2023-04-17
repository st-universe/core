<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Throwable;

abstract class AbstractTickRunner implements TickRunnerInterface
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

    public abstract function runInTransaction(int $batchGroup, int $batchGroupCount): void;

    public abstract function getTickDescription(): string;

    public function runWithResetCheck(int $batchGroup, int $batchGroupCount): void
    {
        if (!$this->isGameStateReset()) {
            $this->run($batchGroup, $batchGroupCount);
        }
    }

    private function run(int $batchGroup, int $batchGroupCount): void
    {
        $this->entityManager->beginTransaction();

        try {
            $this->runInTransaction($batchGroup, $batchGroupCount);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();

            $tickDescription = $this->getTickDescription();

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

    protected function isGameStateReset(): bool
    {
        return $this->game->getGameState() === GameEnum::CONFIG_GAMESTATE_VALUE_RESET;
    }
}
