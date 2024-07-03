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
    public function __construct(
        private ContainerInterface $dic
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
        $tickRunner = $this->dic->get(ProcessTickRunner::class);
        $tickRunner->run(1, 1);

        $this->io()->ok(
            'Process tick has been executed',
            true
        );
    }
}
