<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Stu\Module\Tick\Colony\ColonyTickRunner;

/**
 * Provides cli method for manual colony ticks
 */
final class ColonyTickCommand extends Command
{
    public function __construct(
        private readonly ColonyTickRunner $tickRunner,
    ) {
        parent::__construct(
            'tick:colony',
            'Runs the colony tick'
        );

        $this
            ->usage(
                '<bold>  $0 tick:colony</end> <comment></end> ## Runs the colony tick<eol/>'
            );
    }

    public function execute(): void
    {
        $this->tickRunner->run(1, 1);

        $this->io()->ok(
            'Colony tick has been executed',
            true
        );
    }
}
