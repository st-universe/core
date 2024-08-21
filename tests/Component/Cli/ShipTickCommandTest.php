<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Application;
use Mockery\MockInterface;
use Override;
use Stu\CliInteractorHelper;
use Stu\Module\Tick\Ship\ShipTickRunner;
use Stu\StuTestCase;

class ShipTickCommandTest extends StuTestCase
{
    /** @var MockInterface&ShipTickRunner */
    private MockInterface $shipTickRunner;

    private ShipTickCommand $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->shipTickRunner = $this->mock(ShipTickRunner::class);

        $this->subject = new ShipTickCommand(
            $this->shipTickRunner
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
                'Ship tick has been executed',
                true
            )
            ->once();

        $this->shipTickRunner->shouldReceive('run')
            ->with(1, 1)
            ->once();

        $this->subject->execute();
    }
}
