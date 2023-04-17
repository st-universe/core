<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Psr\Container\ContainerInterface;
use Stu\Module\Tick\Process\ProcessTickRunner;

/**
 * Provides cli method for manual process ticks
 */
final class ProcessTickCommand extends Command
{
    private ContainerInterface $dic;

    public function __construct(
        ContainerInterface $dic
    ) {
        $this->dic = $dic;

        parent::__construct(
            'tick:process',
            'Runs the process tick'
        );

        $this
            ->usage(
                '<bold>  $0 tick:process</end> <comment></end> ## Runs the process tick<eol/>'
            );
    }

    public function execute(): void
    {
        $tickRunner = $this->dic->get(ProcessTickRunner::class);
        $tickRunner->runWithResetCheck(1, 1);

        $this->io()->ok(
            'Process tick has been executed',
            true
        );
    }
}
