<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Psr\Container\ContainerInterface;
use Stu\Module\Tick\Pirate\PirateTickRunner;

/**
 * Provides cli method for manual pirate ticks
 */
final class PirateTickCommand extends Command
{
    private ContainerInterface $dic;

    public function __construct(
        ContainerInterface $dic
    ) {
        $this->dic = $dic;

        parent::__construct(
            'tick:pirate',
            'Runs the pirate tick'
        );

        $this
            ->usage(
                '<bold>  $0 tick:pirate</end> <comment></end> ## Runs the pirate tick<eol/>'
            );
    }

    public function execute(): void
    {
        $tickRunner = $this->dic->get(PirateTickRunner::class);
        $tickRunner->run(1, 1);

        $this->io()->ok(
            'Pirate tick has been executed',
            true
        );
    }
}
