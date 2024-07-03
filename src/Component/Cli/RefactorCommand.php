<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Psr\Container\ContainerInterface;
use Stu\Component\Refactor\RefactorRunner;

final class RefactorCommand extends Command
{
    public function __construct(
        private ContainerInterface $dic
    ) {
        parent::__construct(
            'refactor:run',
            'Start the needed refactoring'
        );

        $this
            ->usage(
                '<bold>  $0 refactor:run</end> <comment></end> ## Starts the refactoring<eol/>'
            );
    }

    public function execute(): void
    {
        $runner = $this->dic->get(RefactorRunner::class);
        $runner->refactor();

        $this->io()->ok(
            'Refactoring has been executed',
            true
        );
    }
}
