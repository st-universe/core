<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Research\NaniteProductionSetup;
use Stu\Component\Research\TechnologyProductionCompletion;

final class RefactorRunner
{
    public function __construct(
        private TechnologyProductionCompletion $technologyProductionCompletion,
        private NaniteProductionSetup $naniteProductionSetup,
        private EntityManagerInterface $entityManager
    ) {}

    public function refactor(): void
    {
        $this->technologyProductionCompletion->work(true);
        $this->entityManager->flush();
        $module = $this->naniteProductionSetup->setup();
        $this->naniteProductionSetup->transferPending($module);
    }

}
