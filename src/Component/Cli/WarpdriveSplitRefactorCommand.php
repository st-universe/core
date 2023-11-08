<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Psr\Container\ContainerInterface;
use Stu\Component\Ship\Refactor\RefactorWarpdriveSplitRunner;

final class WarpdriveSplitRefactorCommand extends Command
{
    private ContainerInterface $dic;

    public function __construct(
        ContainerInterface $dic
    ) {
        $this->dic = $dic;

        parent::__construct(
            'refactor:split',
            'Moves the warpdrive split from core to drive'
        );

        $this
            ->usage(
                '<bold>  $0 refactor:split</end> <comment></end> ## Refactors warpdrive split<eol/>'
            );
    }

    public function execute(): void
    {
        $runner = $this->dic->get(RefactorWarpdriveSplitRunner::class);
        $runner->refactor();

        $this->io()->ok(
            'Warpdrive split has been refactored',
            true
        );
    }
}
