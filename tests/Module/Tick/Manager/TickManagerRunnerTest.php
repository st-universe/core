<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Manager;

use Mockery\MockInterface;
use Stu\Module\Tick\TickManagerInterface;
use Stu\StuTestCase;

class TickManagerRunnerTest extends StuTestCase
{
    /** @var MockInterface&TickManagerInterface */
    private MockInterface $tickManager;

    private TickManagerRunner $subject;

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


        $this->subject->run();
    }
}
