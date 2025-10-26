<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Application;
use Mockery\MockInterface;
use Stu\CliInteractorHelper;
use Stu\Module\Tick\Colony\ColonyTickRunner;
use Stu\StuTestCase;

class ColonyTickCommandTest extends StuTestCase
{
    private MockInterface&ColonyTickRunner $colonyTickRunner;

    private ColonyTickCommand $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->colonyTickRunner = $this->mock(ColonyTickRunner::class);

        $this->subject = new ColonyTickCommand(
            $this->colonyTickRunner
        );
    }

    public function testExecuteExecutes(): void
    {
        $app = $this->mock(Application::class);
        $interactor = $this->mock(CliInteractorHelper::class);

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

        $this->colonyTickRunner->shouldReceive('run')
            ->with(1, 1)
            ->once();

        $this->subject->execute();
    }
}
