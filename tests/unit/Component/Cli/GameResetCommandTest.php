<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Application;
use Mockery\MockInterface;
use Override;
use Psr\Container\ContainerInterface;
use Stu\CliInteractorHelper;
use Stu\Component\Admin\Reset\ResetManagerInterface;
use Stu\StuTestCase;

class GameResetCommandTest extends StuTestCase
{
    /** @var MockInterface&ContainerInterface */
    private MockInterface $dic;

    private GameResetCommand $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->dic = $this->mock(ContainerInterface::class);

        $this->subject = new GameResetCommand(
            $this->dic
        );
    }

    public function testExecuteExecutes(): void
    {
        $app = $this->mock(Application::class);
        $interactor = $this->mock(CliInteractorHelper::class);
        $resetManager = $this->mock(ResetManagerInterface::class);

        $this->dic->shouldReceive('get')
            ->with(ResetManagerInterface::class)
            ->once()
            ->andReturn($resetManager);

        $this->subject->bind($app);

        $app->shouldReceive('io')
            ->withNoArgs()
            ->once()
            ->andReturn($interactor);

        $interactor->shouldReceive('confirm')
            ->with(
                'Are you sure?',
                'n'
            )
            ->once()
            ->andReturnTrue();
        $interactor->shouldReceive('info')
            ->with('Starting reset...', true)
            ->once();
        $interactor->shouldReceive('info')
            ->with('The game has been resetted', true)
            ->once();

        $resetManager->shouldReceive('performReset')
            ->with($interactor)
            ->once();

        $this->subject->execute();
    }

    public function testExecuteCancelsOnUserCancel(): void
    {
        $app = $this->mock(Application::class);
        $interactor = $this->mock(CliInteractorHelper::class);

        $this->subject->bind($app);

        $app->shouldReceive('io')
            ->withNoArgs()
            ->once()
            ->andReturn($interactor);

        $interactor->shouldReceive('confirm')
            ->with(
                'Are you sure?',
                'n'
            )
            ->once()
            ->andReturnFalse();
        $interactor->shouldReceive('info')
            ->with('No action was taken', true)
            ->once();

        $this->subject->execute();
    }
}
