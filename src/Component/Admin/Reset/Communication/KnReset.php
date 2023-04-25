<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Communication;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class KnReset implements KnResetInterface
{
    private KnPostRepositoryInterface $knPostRepository;

    private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        KnPostRepositoryInterface $knPostRepository,
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        RpgPlotRepositoryInterface $rpgPlotRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->knPostRepository = $knPostRepository;
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->rpgPlotRepository = $rpgPlotRepository;
        $this->entityManager = $entityManager;
    }

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

        foreach ($this->rpgPlotMemberRepository->findAll() as $plotMember) {
            $this->rpgPlotMemberRepository->delete($plotMember);
        }
    }

    /**
     * Deletes all kn postings
     */
    private function deleteKnPostings(): void
    {
        echo "  - deleting kn postings\n";

        foreach ($this->knPostRepository->findAll() as $knPost) {
            $this->knPostRepository->delete($knPost);
        }
    }

    /**
     * Deletes all rpg plots
     */
    private function deleteKnPlots(): void
    {
        echo "  - deleting kn plots\n";

        foreach ($this->rpgPlotRepository->findAll() as $plot) {
            $this->rpgPlotRepository->delete($plot);
        }
    }
}
