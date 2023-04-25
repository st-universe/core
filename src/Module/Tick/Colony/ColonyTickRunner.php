<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Stu\Module\Tick\TickRunnerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;

/**
 * Executes the colony tick (energy and commodity production, etc)
 */
final class ColonyTickRunner implements TickRunnerInterface
{
    private const TICK_DESCRIPTION = "colonytick";

    private ColonyTickManagerInterface $colonyTickManager;

    private TransactionTickRunnerInterface $transactionTickRunner;

    public function __construct(
        ColonyTickManagerInterface $colonyTickManager,
        TransactionTickRunnerInterface $transactionTickRunner
    ) {
        $this->colonyTickManager = $colonyTickManager;
        $this->transactionTickRunner = $transactionTickRunner;
    }

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
