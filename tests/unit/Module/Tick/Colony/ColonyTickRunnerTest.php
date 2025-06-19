<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\StuTestCase;

class ColonyTickRunnerTest extends StuTestCase
{
    private MockInterface&ColonyTickManagerInterface $colonyTickManager;

    private MockInterface&TransactionTickRunnerInterface $transactionTickRunner;

    private ColonyTickRunner $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->colonyTickManager = $this->mock(ColonyTickManagerInterface::class);
        $this->transactionTickRunner = $this->mock(TransactionTickRunnerInterface::class);

        $this->subject = new ColonyTickRunner(
            $this->colonyTickManager,
            $this->transactionTickRunner
        );
    }

    public function testRunExecutesColonyTick(): void
    {
        $batchGroup = 2;
        $batchGroupCount = 5;

        $this->colonyTickManager->shouldReceive('work')
            ->with($batchGroup, $batchGroupCount)
            ->once();

        $this->transactionTickRunner->shouldReceive('runWithResetCheck')
            ->with(
                Mockery::on(function ($callable) use ($batchGroup, $batchGroupCount): bool {
                    if (!is_callable($callable)) {
                        return false;
                    }
                    $callable($batchGroup, $batchGroupCount);
                    return true;
                }),
                "colonytick",
                $batchGroup,
                $batchGroupCount
            )
            ->once();

        $this->subject->run($batchGroup, $batchGroupCount);
    }
}
