<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Override;
use Ahc\Cli\Application;
use Mockery\MockInterface;
use Psr\Container\ContainerInterface;
use Stu\CliInteractorHelper;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunner;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\StuTestCase;

class MaintenanceTickCommandTest extends StuTestCase
{
    /** @var MockInterface&ContainerInterface */
    private MockInterface $dic;

    private MaintenanceTickCommand $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->dic = $this->mock(ContainerInterface::class);

        $this->subject = new MaintenanceTickCommand(
            $this->dic
        );
    }

    public function testExecuteExecutes(): void
    {
        $app = $this->mock(Application::class);
        $interactor = $this->mock(CliInteractorHelper::class);
        $tickRunner = $this->mock(TickRunnerInterface::class);

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

        $this->dic->shouldReceive('get')
            ->with(MaintenanceTickRunner::class)
            ->once()
            ->andReturn($tickRunner);

        $tickRunner->shouldReceive('run')
            ->with(1, 1)
            ->once();

        $this->subject->execute();
    }
}
