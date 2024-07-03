<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Alliance;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class AllianceReset implements AllianceResetInterface
{
    public function __construct(private AllianceRepositoryInterface $allianceRepository, private AllianceBoardRepositoryInterface $allianceBoardRepository, private AllianceJobRepositoryInterface $allianceJobRepository, private AllianceRelationRepositoryInterface $allianceRelationRepository, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function deleteAllAllianceBoards(): void
    {
        echo "  - deleting all alliance boards\n";

        $this->allianceBoardRepository->truncateAllAllianceBoards();

        $this->entityManager->flush();
    }

    #[Override]
    public function deleteAllAllianceJobs(): void
    {
        echo "  - deleting alliance jobs\n";

        $this->allianceJobRepository->truncateAllAllianceJobs();

        $this->entityManager->flush();
    }

    #[Override]
    public function deleteAllAllianceRelations(): void
    {
        echo "  - deleting alliance relations\n";

        $this->allianceRelationRepository->truncateAllAllianceRelations();

        $this->entityManager->flush();
    }

    #[Override]
    public function deleteAllAlliances(): void
    {
        echo "  - deleting all alliances\n";

        $this->entityManager->getConnection()->executeQuery('update stu_user set allys_id = null');

        $this->allianceRepository->truncateAllAlliances();

        $this->entityManager->flush();
    }
}
