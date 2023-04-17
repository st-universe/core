<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tick\AbstractTickRunner;

/**
 * Executes the colony tick (energy and commodity production, etc)
 */
final class ColonyTickRunner extends AbstractTickRunner
{
    private ColonyTickManagerInterface $colonyTickManager;

    public function __construct(
        GameControllerInterface $game,
        EntityManagerInterface $entityManager,
        ColonyTickManagerInterface $colonyTickManager,
        FailureEmailSenderInterface $failureEmailSender
    ) {
        parent::__construct($game, $entityManager, $failureEmailSender);
        $this->colonyTickManager = $colonyTickManager;
    }

    public function runInTransaction(int $batchGroup, int $batchGroupCount): void
    {
        $this->colonyTickManager->work($batchGroup, $batchGroupCount);
    }

    public function getTickDescription(): string
    {
        return "colonytick";
    }
}
