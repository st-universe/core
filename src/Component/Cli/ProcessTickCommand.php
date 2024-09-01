<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Stu\Module\Tick\Process\ProcessTickRunner;

/**
 * Provides cli method for manual process ticks
 */
final class ProcessTickCommand extends Command
{
    public function __construct(
        private ProcessTickRunner $tickRunner,
    ) {
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
        $this->tickRunner->run(1, 1);

        $this->io()->ok(
            'Process tick has been executed',
            true
        );
    }
}
