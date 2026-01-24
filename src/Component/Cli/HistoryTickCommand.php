<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Stu\Module\Tick\History\HistoryTickRunner;

/**
 * Provides cli method for manual history ticks
 */
final class HistoryTickCommand extends Command
{
    public function __construct(
        private HistoryTickRunner $tickRunner,
    ) {
        parent::__construct(
            'tick:history',
            'Runs the history tick'
        );

        $this
            ->usage(
                '<bold>  $0 tick:history</end> <comment></end> ## Runs the history tick<eol/>'
            );
    }

    public function execute(): void
    {
        $this->tickRunner->run(1, 1);

        $this->io()->ok(
            'History tick has been executed',
            true
        );
    }
}
