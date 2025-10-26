<?php

declare(strict_types=1);

namespace Component\Cli;

use Ahc\Cli\Application;
use Mockery\MockInterface;
use Stu\CliInteractorHelper;
use Stu\Component\Cli\ColonyCorrectionCommand;
use Stu\Module\Colony\Lib\ColonyCorrector;
use Stu\StuTestCase;

class ColonyCorrectionCommandTest extends StuTestCase
{
    private MockInterface&ColonyCorrector $colonyCorrector;

    private ColonyCorrectionCommand $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->colonyCorrector = $this->mock(ColonyCorrector::class);

        $this->subject = new ColonyCorrectionCommand($this->colonyCorrector);
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
                'Korrektur der Kolonien wurde durchgeführt.',
                true
            )
            ->once();

        $this->colonyCorrector->shouldReceive('correct')
            ->withNoArgs()
            ->once();

        $this->subject->execute();
    }
}
