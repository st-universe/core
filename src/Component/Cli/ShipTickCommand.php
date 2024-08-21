<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Stu\Module\Tick\Ship\ShipTickRunner;

/**
 * Provides cli method for manual ship ticks
 */
final class ShipTickCommand extends Command
{
    public function __construct(
        private readonly ShipTickRunner $tickRunner,
    ) {
        parent::__construct(
            'tick:ship',
            'Runs the ship tick'
        );

        $this
            ->usage(
                '<bold>  $0 tick:ship</end> <comment></end> ## Runs the ship tick<eol/>'
            );
    }

    public function execute(): void
    {
        $this->tickRunner->run(1, 1);

        $this->io()->ok(
            'Ship tick has been executed',
            true
        );
    }
}
