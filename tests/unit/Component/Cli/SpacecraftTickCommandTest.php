<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Application;
use Mockery\MockInterface;
use Override;
use Stu\CliInteractorHelper;
use Stu\Module\Tick\Spacecraft\SpacecraftTickRunner;
use Stu\StuTestCase;

class SpacecraftTickCommandTest extends StuTestCase
{
    private MockInterface&SpacecraftTickRunner $spacecraftTickRunner;

    private SpacecraftTickCommand $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->spacecraftTickRunner = $this->mock(SpacecraftTickRunner::class);

        $this->subject = new SpacecraftTickCommand(
            $this->spacecraftTickRunner
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
                'Spacecraft tick has been executed',
                true
            )
            ->once();

        $this->spacecraftTickRunner->shouldReceive('run')
            ->with(1, 1)
            ->once();

        $this->subject->execute();
    }
}
