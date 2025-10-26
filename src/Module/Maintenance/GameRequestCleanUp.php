<?php

namespace Stu\Module\Maintenance;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Game\TimeConstants;
use Stu\Orm\Entity\GameRequest;

final class GameRequestCleanUp implements MaintenanceHandlerInterface
{
    public const int DECREASE_AMOUNT_PER_DAY = 20;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[\Override]
    public function handle(): void
    {
        $this->entityManager
            ->createQuery(
                sprintf(
                    'DELETE FROM %s gr
                    WHERE gr.time < :threshold',
                    GameRequest::class
                )
            )
            ->setParameter('threshold', time() - TimeConstants::ONE_DAY_IN_SECONDS * 183)
            ->execute();
    }
}
