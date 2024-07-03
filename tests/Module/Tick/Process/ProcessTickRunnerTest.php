<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Mockery;
use Mockery\MockInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\StuTestCase;

class ProcessTickRunnerTest extends StuTestCase
{
    /** @var MockInterface&TransactionTickRunnerInterface */
    private MockInterface $transactionTickRunner;

    /** @var MockInterface&ProcessTickHandlerInterface */
    private MockInterface $handler;

    private ProcessTickRunner $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->transactionTickRunner = $this->mock(TransactionTickRunnerInterface::class);

        $this->handler = $this->mock(ProcessTickHandlerInterface::class);

        $this->subject = new ProcessTickRunner(
            $this->transactionTickRunner,
            [
                $this->handler
            ]
        );
    }

    public function testRunRuns(): void
    {
        $batchGroup = 2;
        $batchGroupCount = 5;

        $this->handler->shouldReceive('work')
            ->withNoArgs()
            ->once();

        $this->transactionTickRunner->shouldReceive('runWithResetCheck')
            ->with(
                Mockery::on(function ($callable): bool {
                    if (!is_callable($callable)) {
                        return false;
                    }
                    $callable();
                    return true;
                }),
                "processtick",
                $batchGroup,
                $batchGroupCount
            )
            ->once();


        $this->subject->run($batchGroup, $batchGroupCount);
    }
}
