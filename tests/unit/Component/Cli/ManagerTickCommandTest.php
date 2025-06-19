<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Application;
use Mockery\MockInterface;
use Override;
use Psr\Container\ContainerInterface;
use Stu\CliInteractorHelper;
use Stu\Module\Tick\Manager\TickManagerRunner;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\StuTestCase;

class ManagerTickCommandTest extends StuTestCase
{
    private MockInterface&ContainerInterface $dic;

    private ManagerTickCommand $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->dic = $this->mock(ContainerInterface::class);

        $this->subject = new ManagerTickCommand(
            $this->dic
        );
    }

    public function testExecuteExecutes(): void
    {
        $app = $this->mock(Application::class);
        $interactor = $this->mock(CliInteractorHelper::class);
        $colonyTickRunner = $this->mock(TickRunnerInterface::class);

        $this->subject->bind($app);

        $app->shouldReceive('io')
            ->withNoArgs()
            ->once()
            ->andReturn($interactor);

        $interactor->shouldReceive('ok')
            ->with(
                'Manager tick has been executed',
                true
            )
            ->once();

        $this->dic->shouldReceive('get')
            ->with(TickManagerRunner::class)
            ->once()
            ->andReturn($colonyTickRunner);

        $colonyTickRunner->shouldReceive('run')
            ->with(1, 1)
            ->once();

        $this->subject->execute();
    }
}
