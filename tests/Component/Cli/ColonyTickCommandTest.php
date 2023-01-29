<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Application;
use Mockery\MockInterface;
use Psr\Container\ContainerInterface;
use Stu\CliInteractorHelper;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;
use Stu\StuTestCase;

class ColonyTickCommandTest extends StuTestCase
{
    /** @var MockInterface&ContainerInterface */
    private MockInterface $dic;

    private ColonyTickCommand $subject;

    protected function setUp(): void
    {
        $this->dic = $this->mock(ContainerInterface::class);

        $this->subject = new ColonyTickCommand(
            $this->dic
        );
    }

    public function testExecuteErrorsDueToUnMappableFaction(): void
    {
        $app = $this->mock(Application::class);
        $interactor = $this->mock(CliInteractorHelper::class);
        $colonyTickManager = $this->mock(ColonyTickManagerInterface::class);

        $tickNumber = 666;

        $this->subject->bind($app);

        $app->shouldReceive('io')
            ->withNoArgs()
            ->once()
            ->andReturn($interactor);

        $interactor->shouldReceive('ok')
            ->with(
                sprintf('Tick for colonies having tick number `%d` has been executed', $tickNumber),
                true
            )
            ->once();

        $this->dic->shouldReceive('get')
            ->with(ColonyTickManagerInterface::class)
            ->once()
            ->andReturn($colonyTickManager);

        $colonyTickManager->shouldReceive('work')
            ->with($tickNumber)
            ->once();

        $this->subject->execute($tickNumber);
    }
}
