<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Psr\Container\ContainerInterface;
use Stu\Module\Tick\Manager\TickManagerRunner;

/**
 * Provides cli method for manual manager ticks
 */
final class ManagerTickCommand extends Command
{
    private ContainerInterface $dic;

    public function __construct(
        ContainerInterface $dic
    ) {
        $this->dic = $dic;

        parent::__construct(
            'tick:manager',
            'Runs the manager tick'
        );

        $this
            ->usage(
                '<bold>  $0 tick:manager</end> <comment></end> ## Runs the manager tick<eol/>'
            );
    }

    public function execute(): void
    {
        $tickRunner = $this->dic->get(TickManagerRunner::class);
        $tickRunner->run();

        $this->io()->ok(
            'Manager tick has been executed',
            true
        );
    }
}
