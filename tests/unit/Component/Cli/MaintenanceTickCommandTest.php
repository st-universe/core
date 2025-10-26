<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Application;
use Mockery\MockInterface;
use Stu\CliInteractorHelper;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunner;
use Stu\StuTestCase;

class MaintenanceTickCommandTest extends StuTestCase
{
    private MockInterface&MaintenanceTickRunner $tickRunner;

    private MaintenanceTickCommand $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->tickRunner = $this->mock(MaintenanceTickRunner::class);

        $this->subject = new MaintenanceTickCommand($this->tickRunner);
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
                'Maintenance tick has been executed',
                true
            )
            ->once();

        $this->tickRunner->shouldReceive('run')
            ->with(1, 1)
            ->once();

        $this->subject->execute();
    }
}
