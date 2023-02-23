<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Throwable;

/**
 * Executes all process related tasks (e.g. finishing build processes, ...)
 */
final class ProcessTickRunner implements TickRunnerInterface
{
    private EntityManagerInterface $entityManager;
    private FailureEmailSenderInterface $failureEmailSender;

    /** @var array<ProcessTickHandlerInterface> */
    private array $handlerList;

    /**
     * @param array<ProcessTickHandlerInterface> $handlerList
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FailureEmailSenderInterface $failureEmailSender,
        array $handlerList
    ) {
        $this->handlerList = $handlerList;
        $this->entityManager = $entityManager;
        $this->failureEmailSender = $failureEmailSender;
    }

    public function run(): void
    {
        $this->entityManager->beginTransaction();

        try {
            foreach ($this->handlerList as $process) {
                $process->work();
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();

            $this->failureEmailSender->sendMail(
                'stu processtick failure',
                sprintf(
                    "Current system time: %s\nThe processtick cron caused an error:\n\n%s\n\n%s",
                    date('Y-m-d H:i:s'),
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );

            throw $e;
        }
    }
}
