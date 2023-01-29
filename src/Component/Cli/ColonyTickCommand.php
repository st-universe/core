<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Psr\Container\ContainerInterface;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;

/**
 * Provides cli method for colony methods
 */
final class ColonyTickCommand extends Command
{
    private ContainerInterface $dic;

    public function __construct(
        ContainerInterface $dic
    ) {
        $this->dic = $dic;

        parent::__construct(
            'tick:colony',
            'Runs the colony tick'
        );

        $this
            ->argument(
                '<ticknumber>',
                'Tick number',
                1
            )
            ->usage(
                '<bold>  $0 tick:colony 666</end> <comment></end> ## Runs the colony tick for tick number 666<eol/>'
            );
    }

    public function execute(int $ticknumber): void
    {
        $tickManager = $this->dic->get(ColonyTickManagerInterface::class);
        $tickManager->work($ticknumber);

        $this->io()->ok(
            sprintf('Tick for colonies having tick number `%d` has been executed', $ticknumber),
            true
        );
    }
}
