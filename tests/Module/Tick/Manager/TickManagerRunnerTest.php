<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Manager;

use Override;
use Mockery\MockInterface;
use Stu\Module\Tick\TickManagerInterface;
use Stu\StuTestCase;

class TickManagerRunnerTest extends StuTestCase
{
    /** @var MockInterface&TickManagerInterface */
    private MockInterface $tickManager;

    private TickManagerRunner $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->tickManager = $this->mock(TickManagerInterface::class);

        $this->subject = new TickManagerRunner(
            $this->tickManager
        );
    }

    public function testRunRuns(): void
    {
        $this->tickManager->shouldReceive('work')
            ->withNoArgs()
            ->once();


        $this->subject->run(1, 1);
    }
}
