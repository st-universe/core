<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

/**
 * Service class to check if users may apply for an alliance
 */
final class AllianceUserApplicationChecker implements AllianceUserApplicationCheckerInterface
{
    private AllianceJobRepositoryInterface $allianceJobRepository;

    public function __construct(
        AllianceJobRepositoryInterface $allianceJobRepository
    ) {
        $this->allianceJobRepository = $allianceJobRepository;
    }

    public function mayApply(
        UserInterface $user,
        AllianceInterface $alliance
    ): bool {
        $pendingApplication = $this->allianceJobRepository->getByUserAndAllianceAndType(
            $user,
            $alliance,
            AllianceEnum::ALLIANCE_JOBS_PENDING
        );
        if ($pendingApplication !== null) {
            return false;
        }

        return $alliance->getAcceptApplications()
            && $user->getAlliance() === null
            && ($alliance->getFaction() === null
                || $user->getFaction() === $alliance->getFaction()
            );
    }
}
