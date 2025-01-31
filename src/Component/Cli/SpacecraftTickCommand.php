<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Stu\Module\Tick\Spacecraft\SpacecraftTickRunner;

/**
 * Provides cli method for manual spacecraft ticks
 */
final class SpacecraftTickCommand extends Command
{
    public function __construct(
        private readonly SpacecraftTickRunner $tickRunner,
    ) {
        parent::__construct(
            'tick:spacecraft',
            'Runs the spacecraft tick'
        );

        $this
            ->usage(
                '<bold>  $0 tick:spacecraft</end> <comment></end> ## Runs the spacecraft tick<eol/>'
            );
    }

    public function execute(): void
    {
        $this->tickRunner->run(1, 1);

        $this->io()->ok(
            'Spacecraft tick has been executed',
            true
        );
    }
}
