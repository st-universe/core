<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Communication;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class KnReset implements KnResetInterface
{
    public function __construct(private KnPostRepositoryInterface $knPostRepository, private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository, private RpgPlotRepositoryInterface $rpgPlotRepository, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function resetKn(): void
    {
        $this->deleteKnPlotMembers();
        $this->deleteKnPostings();
        $this->deleteKnPlots();

        $this->entityManager->flush();
    }

    /**
     * Deletes all rpg plot members
     */
    private function deleteKnPlotMembers(): void
    {
        echo "  - deleting kn plot members\n";

        $this->rpgPlotMemberRepository->truncateAllEntities();
    }

    /**
     * Deletes all kn postings
     */
    private function deleteKnPostings(): void
    {
        echo "  - deleting kn postings\n";

        $this->knPostRepository->truncateAllEntities();
    }

    /**
     * Deletes all rpg plots
     */
    private function deleteKnPlots(): void
    {
        echo "  - deleting kn plots\n";

        $this->rpgPlotRepository->truncateAllEntities();
    }
}
