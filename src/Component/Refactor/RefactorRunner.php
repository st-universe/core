<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceMemberJob;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceMemberJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\AllianceSettingsRepositoryInterface;
use Stu\Component\Alliance\AllianceSettingsEnum;

final class RefactorRunner
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AllianceJobRepositoryInterface $allianceJobRepository,
        private AllianceRepositoryInterface $allianceRepository,
        private AllianceSettingsRepositoryInterface $allianceSettingsRepository
    ) {}

    public function refactor(): void
    {
        echo "\n=== Alliance Job System Migration Start ===\n\n";
        $this->migrateAllianceJobSystem();
        echo "\n=== Alliance Job System Migration Complete ===\n";
    }

    private function migrateAllianceJobSystem(): void
    {
        $connection = $this->entityManager->getConnection();

        $oldJobs = $connection->fetchAllAssociative(
            'SELECT id, alliance_id, user_id, type FROM stu_alliances_jobs WHERE type != :pending ORDER BY alliance_id, type',
            ['pending' => AllianceJobTypeEnum::PENDING->value]
        );

        echo "Found " . count($oldJobs) . " existing jobs (excluding pending)\n";

        $processedAlliances = [];
        $jobMapping = [];

        foreach ($oldJobs as $oldJob) {
            $allianceId = (int) $oldJob['alliance_id'];
            $userId = (int) $oldJob['user_id'];
            $type = (int) $oldJob['type'];

            if (!isset($processedAlliances[$allianceId])) {
                $processedAlliances[$allianceId] = [];
            }

            if (!isset($processedAlliances[$allianceId][$type])) {
                $title = $this->getTitleForType($allianceId, $type);

                $newJob = new AllianceJob();
                $alliance = $this->allianceRepository->find($allianceId);
                if ($alliance === null) {
                    continue;
                }

                $newJob->setAlliance($alliance);
                $newJob->setTitle($title);
                $newJob->setSort($this->getSortForType($type));
                $newJob->setIsFounderPermission($type === AllianceJobTypeEnum::FOUNDER->value);
                $newJob->setIsSuccessorPermission($type === AllianceJobTypeEnum::SUCCESSOR->value);
                $newJob->setIsDiplomaticPermission($type === AllianceJobTypeEnum::DIPLOMATIC->value);

                $this->entityManager->persist($newJob);
                $this->entityManager->flush();

                $processedAlliances[$allianceId][$type] = $newJob->getId();
                $jobMapping[$oldJob['id']] = $newJob->getId();

                echo "  Created new job '{$title}' (ID: {$newJob->getId()}) for Alliance {$allianceId}\n";
            }

            $jobId = $processedAlliances[$allianceId][$type];
            $newJob = $this->allianceJobRepository->find($jobId);
            $user = $this->entityManager->getReference('Stu\\Orm\\Entity\\User', $userId);

            if ($newJob !== null && $user !== null) {
                $memberJob = new AllianceMemberJob();
                $memberJob->setJob($newJob);
                $memberJob->setUser($user);

                $this->entityManager->persist($memberJob);
            }
        }

        $this->entityManager->flush();

        echo "\nProcessing pending applications...\n";

        $pendingApplications = $connection->fetchAllAssociative(
            'SELECT id, alliance_id, user_id FROM stu_alliances_jobs WHERE type = :pending',
            ['pending' => AllianceJobTypeEnum::PENDING->value]
        );

        echo "Found " . count($pendingApplications) . " pending applications\n";

        foreach ($pendingApplications as $pending) {
            $allianceId = (int) $pending['alliance_id'];
            $userId = (int) $pending['user_id'];

            $alliance = $this->allianceRepository->find($allianceId);
            $user = $this->entityManager->getReference('Stu\\Orm\\Entity\\User', $userId);

            if ($alliance === null || $user === null) {
                continue;
            }

            if (!isset($processedAlliances[$allianceId]['pending'])) {
                $pendingJob = new AllianceJob();
                $pendingJob->setAlliance($alliance);
                $pendingJob->setTitle('Bewerber');
                $pendingJob->setSort(999);
                $pendingJob->setIsFounderPermission(false);
                $pendingJob->setIsSuccessorPermission(false);
                $pendingJob->setIsDiplomaticPermission(false);

                $this->entityManager->persist($pendingJob);
                $this->entityManager->flush();

                $processedAlliances[$allianceId]['pending'] = $pendingJob->getId();

                echo "  Created 'Bewerber' job (ID: {$pendingJob->getId()}) for Alliance {$allianceId}\n";
            }

            $jobId = $processedAlliances[$allianceId]['pending'];
            $pendingJob = $this->allianceJobRepository->find($jobId);

            if ($pendingJob !== null) {
                $memberJob = new AllianceMemberJob();
                $memberJob->setJob($pendingJob);
                $memberJob->setUser($user);

                $this->entityManager->persist($memberJob);
            }
        }

        $this->entityManager->flush();

        $totalJobs = count(array_unique(array_merge(...array_values($processedAlliances))));
        echo "\nSummary:\n";
        echo "  - Alliances processed: " . count($processedAlliances) . "\n";
        echo "  - Jobs created: {$totalJobs}\n";
        echo "  - Member assignments created\n";
    }

    private function getSortForType(int $type): int
    {
        return match ($type) {
            AllianceJobTypeEnum::FOUNDER->value => 1,
            AllianceJobTypeEnum::SUCCESSOR->value => 2,
            AllianceJobTypeEnum::DIPLOMATIC->value => 3,
            default => 999,
        };
    }

    private function getTitleForType(int $allianceId, int $type): string
    {
        $settingEnum = match ($type) {
            AllianceJobTypeEnum::FOUNDER->value => AllianceSettingsEnum::ALLIANCE_FOUNDER_DESCRIPTION,
            AllianceJobTypeEnum::SUCCESSOR->value => AllianceSettingsEnum::ALLIANCE_SUCCESSOR_DESCRIPTION,
            AllianceJobTypeEnum::DIPLOMATIC->value => AllianceSettingsEnum::ALLIANCE_DIPLOMATIC_DESCRIPTION,
            default => null,
        };

        if ($settingEnum === null) {
            return match ($type) {
                AllianceJobTypeEnum::FOUNDER->value => 'Präsident',
                AllianceJobTypeEnum::SUCCESSOR->value => 'Vize-Präsident',
                AllianceJobTypeEnum::DIPLOMATIC->value => 'Außenminister',
                default => 'Mitglied',
            };
        }

        $settings = $this->allianceSettingsRepository->findBy([
            'alliance' => $allianceId,
            'setting' => $settingEnum,
        ]);

        if (count($settings) > 0) {
            return $settings[0]->getValue();
        }

        return match ($type) {
            AllianceJobTypeEnum::FOUNDER->value => 'Präsident',
            AllianceJobTypeEnum::SUCCESSOR->value => 'Vize-Präsident',
            AllianceJobTypeEnum::DIPLOMATIC->value => 'Außenminister',
            default => 'Mitglied',
        };
    }
}
