<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SaveJobs;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Edit\Edit;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class SaveJobs implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SAVE_ALLIANCE_JOBS';

    public function __construct(
        private SaveJobsRequestInterface $saveJobsRequest,
        private AllianceJobRepositoryInterface $allianceJobRepository,
        private AllianceActionManagerInterface $allianceActionManager,
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $user)) {
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
        $founderJob = null;

        foreach ($jobsData as $jobData) {
            if (!isset($jobData['id'], $jobData['title'], $jobData['sort'])) {
                continue;
            }

            $jobId = $jobData['id'];
            $title = trim($jobData['title']);
            $sort = (int) $jobData['sort'];
            $isSuccessor = $jobData['is_successor'] ?? false;
            $isDiplomatic = $jobData['is_diplomatic'] ?? false;

            if (strlen($title) < 3) {
                $game->getInfo()->addInformation('Alle Rollenbezeichnungen müssen mindestens 3 Zeichen lang sein');
                return;
            }

            if ($isSuccessor && $isDiplomatic) {
                $game->getInfo()->addInformation('Eine Rolle kann nicht gleichzeitig Vize und Diplomat sein');
                return;
            }

            if (str_starts_with($jobId, 'new_')) {
                $newJob = new AllianceJob();
                $newJob->setAlliance($alliance);
                $newJob->setTitle($title);
                $newJob->setSort($sort);
                $newJob->setIsFounderPermission(false);
                $newJob->setIsSuccessorPermission($isSuccessor);
                $newJob->setIsDiplomaticPermission($isDiplomatic);

                $this->allianceJobRepository->save($newJob);
                $this->entityManager->flush();

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

                    if ($job->hasFounderPermission()) {
                        $founderJob = $job;
                        if ($sort !== 1) {
                            $job->setSort(1);
                        }
                    } else {
                        $job->setIsSuccessorPermission($isSuccessor);
                        $job->setIsDiplomaticPermission($isDiplomatic);
                    }

                    $this->allianceJobRepository->save($job);
                    $processedJobIds[] = $jobId;
                }
            }
        }

        foreach ($existingJobs as $jobId => $job) {
            if (!in_array($jobId, $processedJobIds) && !$job->hasFounderPermission()) {
                $this->allianceJobRepository->delete($job);
            }
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($alliance);

        $game->getInfo()->addInformation('Die Allianz-Rollen wurden gespeichert');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
