<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Override;
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

/**
 * Service class to check if users may apply for an alliance
 */
final class AllianceUserApplicationChecker implements AllianceUserApplicationCheckerInterface
{
    public function __construct(private AllianceJobRepositoryInterface $allianceJobRepository) {}

    #[Override]
    public function mayApply(
        User $user,
        Alliance $alliance
    ): bool {
        $pendingApplication = $this->allianceJobRepository->getByUserAndAllianceAndType(
            $user,
            $alliance,
            AllianceJobTypeEnum::PENDING
        );
        if ($pendingApplication !== null) {
            return false;
        }

        return $alliance->getAcceptApplications()
            && $user->getAlliance() === null
            && (
                $alliance->getFaction() === null
                || $user->getFaction() === $alliance->getFaction()
            );
    }
}
