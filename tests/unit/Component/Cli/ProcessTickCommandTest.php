<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Application;
use Mockery\MockInterface;
use Override;
use Stu\CliInteractorHelper;
use Stu\Module\Tick\Process\ProcessTickRunner;
use Stu\StuTestCase;

class ProcessTickCommandTest extends StuTestCase
{
    private MockInterface&ProcessTickRunner $processTickRunner;

    private ProcessTickCommand $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->processTickRunner = $this->mock(ProcessTickRunner::class);

        $this->subject = new ProcessTickCommand(
            $this->processTickRunner
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
                'Process tick has been executed',
                true
            )
            ->once();

        $this->processTickRunner->shouldReceive('run')
            ->with(1, 1)
            ->once();

        $this->subject->execute();
    }
}
