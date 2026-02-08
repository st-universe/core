<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Stu\Module\Tick\TickRunnerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;

/**
 * Executes the colony tick (energy and commodity production, etc)
 */
class ColonyTickRunner implements TickRunnerInterface
{
    private const string TICK_DESCRIPTION = "colonytick";

    public function __construct(private ColonyTickManagerInterface $colonyTickManager, private TransactionTickRunnerInterface $transactionTickRunner) {}

    #[\Override]
    public function run(int $batchGroup, int $batchGroupCount): void
    {
        $this->transactionTickRunner->runWithResetCheck(
            function (int $batchGroup, int $batchGroupCount): void {
                $this->colonyTickManager->work($batchGroup, $batchGroupCount);
            },
            self::TICK_DESCRIPTION,
            $batchGroup,
            $batchGroupCount
        );
    }
}
