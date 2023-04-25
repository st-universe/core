<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Alliance;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AllianceReset implements AllianceResetInterface
{
    private AllianceRepositoryInterface $allianceRepository;

    private AllianceBoardRepositoryInterface $allianceBoardRepository;

    private AllianceJobRepositoryInterface $allianceJobRepository;

    private AllianceRelationRepositoryInterface $allianceRelationRepository;

    private UserRepositoryInterface $userRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        AllianceRepositoryInterface $allianceRepository,
        AllianceBoardRepositoryInterface $allianceBoardRepository,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        UserRepositoryInterface $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->allianceRepository = $allianceRepository;
        $this->allianceBoardRepository = $allianceBoardRepository;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function deleteAllAllianceBoards(): void
    {
        echo "  - deleting all alliance boards\n";

        foreach ($this->allianceBoardRepository->findAll() as $board) {

            $this->allianceBoardRepository->delete($board);
        }

        $this->entityManager->flush();
    }

    public function deleteAllAllianceJobs(): void
    {
        echo "  - deleting alliance jobs\n";

        foreach ($this->allianceRepository->findAll() as $alliance) {

            foreach ($alliance->getJobs() as $job) {
                $this->allianceJobRepository->delete($job);
            }

            $alliance->getJobs()->clear();
        }

        $this->entityManager->flush();
    }

    public function deleteAllAllianceRelations(): void
    {
        echo "  - deleting alliance relations\n";

        foreach ($this->allianceRepository->findAll() as $alliance) {

            foreach ($this->allianceRelationRepository->getByAlliance($alliance->getId()) as $relation) {
                $this->allianceRelationRepository->delete($relation);
            }
        }

        $this->entityManager->flush();
    }

    public function deleteAllAlliances(): void
    {
        echo "  - deleting all alliances\n";

        foreach ($this->allianceRepository->findAll() as $alliance) {

            foreach ($alliance->getMembers() as $member) {
                $member->setAlliance(null);
                $member->setAllianceId(null);
                $this->userRepository->save($member);
            }

            $alliance->getMembers()->clear();

            $this->allianceRepository->delete($alliance);
        }

        $this->entityManager->flush();
    }
}
