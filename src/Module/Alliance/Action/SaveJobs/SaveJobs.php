<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SaveJobs;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\View\Edit\Edit;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceJobPermission;
use Stu\Orm\Repository\AllianceJobPermissionRepositoryInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class SaveJobs implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SAVE_ALLIANCE_JOBS';

    public function __construct(
        private SaveJobsRequestInterface $saveJobsRequest,
        private AllianceJobRepositoryInterface $allianceJobRepository,
        private AllianceJobPermissionRepositoryInterface $allianceJobPermissionRepository,
        private AllianceJobManagerInterface $allianceJobManager,
        private EntityManagerInterface $entityManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        if (!$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::MANAGE_JOBS)) {
            throw new AccessViolationException();
        }

        $game->setView(Edit::VIEW_IDENTIFIER);

        $jobsData = json_decode($this->saveJobsRequest->getJobsData(), true);

        if (!is_array($jobsData)) {
            $game->getInfo()->addInformation('Fehlerhafte Daten übermittelt');
            return;
        }

        $existingJobs = [];
        foreach ($alliance->getJobs() as $job) {
            $existingJobs[$job->getId()] = $job;
        }

        $processedJobIds = [];

        foreach ($jobsData as $jobData) {
            if (!isset($jobData['id'], $jobData['title'], $jobData['sort'])) {
                continue;
            }

            $jobId = $jobData['id'];
            $title = trim($jobData['title']);
            $sort = (int) $jobData['sort'];
            $permissions = $jobData['permissions'] ?? [];

            if (strlen($title) < 3) {
                $game->getInfo()->addInformation('Alle Rollenbezeichnungen müssen mindestens 3 Zeichen lang sein');
                return;
            }

            if (str_starts_with($jobId, 'new_')) {
                $newJob = new AllianceJob();
                $newJob->setAlliance($alliance);
                $newJob->setTitle($title);
                $newJob->setSort($sort);

                $this->allianceJobRepository->save($newJob);
                $this->entityManager->flush();

                $this->saveJobPermissions($newJob, $permissions);

                $processedJobIds[] = $newJob->getId();
            } else {
                $jobId = (int) $jobId;
                if (isset($existingJobs[$jobId])) {
                    $job = $existingJobs[$jobId];

                    if ($job->getAlliance()->getId() !== $alliance->getId()) {
                        continue;
                    }

                    $job->setTitle($title);
                    $job->setSort($sort);

                    if ($job->hasPermission(AllianceJobPermissionEnum::FOUNDER->value)) {
                        if ($sort !== 1) {
                            $job->setSort(1);
                        }
                    } else {
                        $this->allianceJobPermissionRepository->deleteByJob($job->getId());
                        $this->entityManager->flush();

                        $this->saveJobPermissions($job, $permissions);
                    }

                    $this->allianceJobRepository->save($job);
                    $processedJobIds[] = $jobId;
                }
            }
        }

        foreach ($existingJobs as $jobId => $job) {
            if (!in_array($jobId, $processedJobIds) && !$job->hasPermission(AllianceJobPermissionEnum::FOUNDER->value)) {
                $this->allianceJobRepository->delete($job);
            }
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($alliance);

        $game->getInfo()->addInformation('Die Allianz-Rollen wurden gespeichert');
    }

    /**
     * @param array<int> $permissions
     */
    private function saveJobPermissions(AllianceJob $job, array $permissions): void
    {
        $cleanedPermissions = $this->cleanPermissions($permissions);

        foreach ($cleanedPermissions as $permissionValue) {
            $permission = new AllianceJobPermission();
            $permission->setJob($job);
            $permission->setPermission($permissionValue);
            $this->allianceJobPermissionRepository->save($permission);
        }

        $this->entityManager->flush();
    }

    /**
     * @param array<int> $permissions
     * @return array<int>
     */
    private function cleanPermissions(array $permissions): array
    {
        $permissionEnums = [];
        foreach ($permissions as $permissionValue) {
            $enum = AllianceJobPermissionEnum::tryFrom((int) $permissionValue);
            if ($enum !== null) {
                $permissionEnums[] = $enum;
            }
        }

        $hasSuccessor = in_array(AllianceJobPermissionEnum::SUCCESSOR, $permissionEnums);
        $hasDiplomatic = in_array(AllianceJobPermissionEnum::DIPLOMATIC, $permissionEnums);

        $cleanedPermissions = [];

        foreach ($permissionEnums as $enum) {
            $parent = $enum->getParentPermission();

            if ($parent === AllianceJobPermissionEnum::SUCCESSOR && $hasSuccessor) {
                continue;
            }

            if ($parent === AllianceJobPermissionEnum::DIPLOMATIC && $hasDiplomatic) {
                continue;
            }

            $cleanedPermissions[] = $enum->value;
        }

        $successorChildren = AllianceJobPermissionEnum::SUCCESSOR->getChildPermissions();
        $allSuccessorChildrenPresent = true;
        foreach ($successorChildren as $child) {
            if (!in_array($child, $permissionEnums)) {
                $allSuccessorChildrenPresent = false;
                break;
            }
        }

        if ($allSuccessorChildrenPresent && !$hasSuccessor && count($successorChildren) > 0) {
            $cleanedPermissions = array_filter($cleanedPermissions, function ($value) use ($successorChildren) {
                foreach ($successorChildren as $child) {
                    if ($child->value === $value) {
                        return false;
                    }
                }
                return true;
            });
            $cleanedPermissions[] = AllianceJobPermissionEnum::SUCCESSOR->value;
        }

        $diplomaticChildren = AllianceJobPermissionEnum::DIPLOMATIC->getChildPermissions();
        $allDiplomaticChildrenPresent = true;
        foreach ($diplomaticChildren as $child) {
            if (!in_array($child, $permissionEnums)) {
                $allDiplomaticChildrenPresent = false;
                break;
            }
        }

        if ($allDiplomaticChildrenPresent && !$hasDiplomatic && count($diplomaticChildren) > 0) {
            $cleanedPermissions = array_filter($cleanedPermissions, function ($value) use ($diplomaticChildren) {
                foreach ($diplomaticChildren as $child) {
                    if ($child->value === $value) {
                        return false;
                    }
                }
                return true;
            });
            $cleanedPermissions[] = AllianceJobPermissionEnum::DIPLOMATIC->value;
        }

        return array_values(array_unique($cleanedPermissions));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
