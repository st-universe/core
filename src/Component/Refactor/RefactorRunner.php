<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Orm\Entity\AllianceJobPermission;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class RefactorRunner
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AllianceJobRepositoryInterface $allianceJobRepository
    ) {}

    public function refactor(): void
    {
        echo "\n=== Alliance Job Permission Migration Start ===\n\n";
        $this->migrateJobPermissions();
        echo "\n=== Alliance Job Permission Migration Complete ===\n";
    }

    private function migrateJobPermissions(): void
    {
        $connection = $this->entityManager->getConnection();

        $jobs = $connection->fetchAllAssociative(
            'SELECT id, alliance_id, title, is_founder_permission, is_successor_permission, is_diplomatic_permission 
             FROM stu_alliances_jobs 
             ORDER BY alliance_id, sort'
        );

        echo "Found " . count($jobs) . " jobs to migrate\n\n";

        $migratedCount = 0;
        $permissionsCreated = 0;

        foreach ($jobs as $job) {
            $jobId = (int) $job['id'];
            $allianceId = (int) $job['alliance_id'];
            $title = $job['title'];
            $isFounder = (bool) $job['is_founder_permission'];
            $isSuccessor = (bool) $job['is_successor_permission'];
            $isDiplomatic = (bool) $job['is_diplomatic_permission'];

            $existingPermissions = $connection->fetchAllAssociative(
                'SELECT permission FROM stu_alliance_job_permission WHERE job_id = :jobId',
                ['jobId' => $jobId]
            );

            if (count($existingPermissions) > 0) {
                echo "  Job '{$title}' (ID: {$jobId}) already has permissions, skipping...\n";
                continue;
            }

            $jobEntity = $this->allianceJobRepository->find($jobId);
            if ($jobEntity === null) {
                echo "  Job ID {$jobId} not found, skipping...\n";
                continue;
            }

            $permissionsAdded = [];

            if ($isFounder) {
                $permission = new AllianceJobPermission();
                $permission->setJob($jobEntity);
                $permission->setPermission(AllianceJobPermissionEnum::FOUNDER->value);
                $this->entityManager->persist($permission);
                $permissionsAdded[] = 'FOUNDER';
                $permissionsCreated++;
            }

            if ($isSuccessor) {
                $permission = new AllianceJobPermission();
                $permission->setJob($jobEntity);
                $permission->setPermission(AllianceJobPermissionEnum::SUCCESSOR->value);
                $this->entityManager->persist($permission);
                $permissionsAdded[] = 'SUCCESSOR';
                $permissionsCreated++;
            }

            if ($isDiplomatic) {
                $permission = new AllianceJobPermission();
                $permission->setJob($jobEntity);
                $permission->setPermission(AllianceJobPermissionEnum::DIPLOMATIC->value);
                $this->entityManager->persist($permission);
                $permissionsAdded[] = 'DIPLOMATIC';
                $permissionsCreated++;
            }

            if (count($permissionsAdded) > 0) {
                echo "  Migrated job '{$title}' (ID: {$jobId}, Alliance: {$allianceId})\n";
                echo "    Added permissions: " . implode(', ', $permissionsAdded) . "\n";
                $migratedCount++;
            } else {
                echo "  Job '{$title}' (ID: {$jobId}) has no permissions to migrate\n";
            }
        }

        $this->entityManager->flush();

        echo "\nSummary:\n";
        echo "  - Jobs processed: " . count($jobs) . "\n";
        echo "  - Jobs migrated: {$migratedCount}\n";
        echo "  - Permissions created: {$permissionsCreated}\n";
        echo "\nMigration completed successfully!\n";
    }
}
