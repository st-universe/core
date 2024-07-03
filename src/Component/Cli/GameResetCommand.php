<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Psr\Container\ContainerInterface;
use Stu\Component\Admin\Reset\ResetManagerInterface;

/**
 * Provides cli method for game resets
 */
final class GameResetCommand extends Command
{
    public function __construct(
        private ContainerInterface $dic
    ) {
        parent::__construct(
            'game:reset',
            'Performs a complete reset of the game'
        );

        $this->usage(
            '<bold>  $0 game:reset</end> <comment></end> ## Performs the game reset<eol/>'
        );
    }

    public function execute(): void
    {
        $io = $this->io();

        $confirm = $io->confirm('Are you sure?', 'n');
        if (!$confirm) {
            $io->info('No action was taken', true);
            return;
        }

        $io->info('Starting reset...', true);

        $this->dic->get(ResetManagerInterface::class)->performReset($io);

        $io->info('The game has been resetted', true);
    }
}
