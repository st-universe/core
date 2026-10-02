<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Tick\Process\ProcessTickHandlerInterface;

final class DoManualProcessTick implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PROCESS_TICK';

    /**
     * @param list<ProcessTickHandlerInterface> $tickHandler
     */
    public function __construct(private EntityManagerInterface $entityManager, private array $tickHandler) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowTicks::VIEW_IDENTIFIER);
        foreach ($this->tickHandler as $process) {
            $process->work();
        }

        $this->entityManager->flush();

        $context->getInfo()->addInformation("Der Process-Tick wurde durchgeführt!");
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
