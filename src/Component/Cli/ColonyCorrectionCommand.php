<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Stu\Module\Colony\Lib\ColonyCorrectorInterface;

/**
 * Provides cli method for colony correction
 */
final class ColonyCorrectionCommand extends Command
{
    public function __construct(
        private readonly ColonyCorrectorInterface $colonyCorrector,
    ) {
        parent::__construct(
            'colony:correct',
            'Corrects colonies'
        );

        $this
            ->usage(
                '<bold>  $0 colony:correct</end> <comment></end> ## Corrects colonies<eol/>'
            );
    }

    public function execute(): void
    {
        $this->colonyCorrector->correct();

        $this->io()->ok('Korrektur der Kolonien wurde durchgeführt.', true);
    }
}
