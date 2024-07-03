<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Override;
use Ahc\Cli\Application;
use Mockery\MockInterface;
use Psr\Container\ContainerInterface;
use Stu\CliInteractorHelper;
use Stu\Module\Tick\Colony\ColonyTickRunner;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\StuTestCase;

class ColonyTickCommandTest extends StuTestCase
{
    /** @var MockInterface&ContainerInterface */
    private MockInterface $dic;

    private ColonyTickCommand $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->dic = $this->mock(ContainerInterface::class);

        $this->subject = new ColonyTickCommand(
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
                'Colony tick has been executed',
                true
            )
            ->once();

        $this->dic->shouldReceive('get')
            ->with(ColonyTickRunner::class)
            ->once()
            ->andReturn($colonyTickRunner);

        $colonyTickRunner->shouldReceive('run')
            ->with(1, 1)
            ->once();

        $this->subject->execute();
    }
}
