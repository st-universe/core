<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Psr\Container\ContainerInterface;
use Stu\Component\StarSystem\GenerateEmptySystemsInterface;

/**
 * Provides cli method for generation of empty star systems
 */
final class GenerateEmptySystemsCommand extends Command
{
    private ContainerInterface $dic;

    public function __construct(
        ContainerInterface $dic
    ) {
        $this->dic = $dic;

        parent::__construct(
            'system:generate',
            'Generates empty systems'
        );

        $this
            ->argument(
                '<layerid>',
                'Id of the map layer'
            )
            ->usage(
                '<bold>  $0 system:generate 1</end> <comment></end> ## Generates star systems for the layer<eol/>'
            );
    }

    public function execute(int $layerid): void
    {
        $io = $this->io();

        $component = $this->dic->get(GenerateEmptySystemsInterface::class);

        $count = $component->generate($layerid, null);

        $io->ok(
            sprintf('Es wurden %d Systeme generiert.', $count),
            true
        );
    }
}
