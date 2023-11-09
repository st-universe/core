<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use Psr\Container\ContainerInterface;
use Stu\Component\Ship\Refactor\RefactorReactorRunner;

final class ReactorRefactorCommand extends Command
{
    private ContainerInterface $dic;

    public function __construct(
        ContainerInterface $dic
    ) {
        $this->dic = $dic;

        parent::__construct(
            'refactor:reactor',
            'Moves reactor values from ship to system data'
        );

        $this
            ->usage(
                '<bold>  $0 refactor:reactor</end> <comment></end> ## Refactors reactor values<eol/>'
            );
    }

    public function execute(): void
    {
        $runner = $this->dic->get(RefactorReactorRunner::class);
        $runner->refactor();

        $this->io()->ok(
            'Reactor values have been refactored',
            true
        );
    }
}
