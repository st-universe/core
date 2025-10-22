<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Override;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceApplicationRepositoryInterface;

/**
 * Service class to check if users may apply for an alliance
 */
final class AllianceUserApplicationChecker implements AllianceUserApplicationCheckerInterface
{
    public function __construct(
        private AllianceApplicationRepositoryInterface $allianceApplicationRepository
    ) {}

    #[Override]
    public function mayApply(
        User $user,
        Alliance $alliance
    ): bool {
        $existingApplication = $this->allianceApplicationRepository->getByUserAndAlliance($user, $alliance);
        if ($existingApplication !== null) {
            return false;
        }

        return $alliance->getAcceptApplications()
            && $user->getAlliance() === null
            && (
                $alliance->getFaction() === null
                || $user->getFaction()->getId() === $alliance->getFaction()->getId()
            );
    }
}
