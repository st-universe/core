<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tick\AbstractTickRunner;

/**
 * Executes all process related tasks (e.g. finishing build processes, ...)
 */
final class ProcessTickRunner extends AbstractTickRunner
{
    /** @var array<ProcessTickHandlerInterface> */
    private array $handlerList;

    /**
     * @param array<ProcessTickHandlerInterface> $handlerList
     */
    public function __construct(
        GameControllerInterface $game,
        EntityManagerInterface $entityManager,
        FailureEmailSenderInterface $failureEmailSender,
        array $handlerList
    ) {
        parent::__construct($game, $entityManager, $failureEmailSender);

        $this->handlerList = $handlerList;
    }

    public function runInTransaction(int $batchGroup, int $batchGroupCount): void
    {
        foreach ($this->handlerList as $process) {
            $process->work();
        }
    }

    public function getTickDescription(): string
    {
        return "processtick";
    }
}
