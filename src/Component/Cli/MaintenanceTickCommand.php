<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Psr\Container\ContainerInterface;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunner;

/**
 * Provides cli method for manual maintenance ticks
 */
final class MaintenanceTickCommand extends Command
{
    private ContainerInterface $dic;

    public function __construct(
        ContainerInterface $dic
    ) {
        $this->dic = $dic;

        parent::__construct(
            'tick:maintenance',
            'Runs the maintenance tick'
        );

        $this
            ->usage(
                '<bold>  $0 tick:maintenance</end> <comment></end> ## Runs the maintenance tick<eol/>'
            );
    }

    public function execute(): void
    {
        $tickRunner = $this->dic->get(MaintenanceTickRunner::class);
        $tickRunner->run(1, 1);

        $this->io()->ok(
            'Maintenance tick has been executed',
            true
        );
    }
}
