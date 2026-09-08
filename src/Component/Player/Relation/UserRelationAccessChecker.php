<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;

final class UserRelationAccessChecker
{
    public function __construct(private readonly AllianceJobManagerInterface $allianceJobManager) {}

    public function getRepresentedParty(User $user): User|Alliance|null
    {
        $alliance = $user->getAlliance();
        if ($alliance === null) {
            return $user;
        }

        return $this->canCreateForAlliance($user, $alliance) ? $alliance : null;
    }

    public function canManageRelations(User $user): bool
    {
        $alliance = $user->getAlliance();

        return $alliance === null || $this->canManageForAlliance($user, $alliance);
    }

    public function canCreateForParty(User $actor, User|Alliance $party): bool
    {
        if ($party instanceof User) {
            return $actor->getAlliance() === null && $actor->getId() === $party->getId();
        }

        return $actor->getAlliance()?->getId() === $party->getId()
            && $this->canCreateForAlliance($actor, $party);
    }

    public function canRepresentParty(User $actor, User|Alliance $party): bool
    {
        if ($party instanceof User) {
            return $actor->getAlliance() === null && $actor->getId() === $party->getId();
        }

        return $actor->getAlliance()?->getId() === $party->getId()
            && $this->canManageForAlliance($actor, $party);
    }

    public function hasValidParties(User|Alliance $source, User|Alliance $recipient): bool
    {
        if ($source instanceof User && $recipient instanceof User) {
            return $source->getId() !== $recipient->getId()
                && $source->getAlliance() === null
                && $recipient->getAlliance() === null;
        }

        if ($source instanceof Alliance && $recipient instanceof User) {
            return $recipient->getAlliance() === null;
        }

        return $source instanceof User && $source->getAlliance() === null;
    }

    private function canCreateForAlliance(User $user, Alliance $alliance): bool
    {
        return $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::CREATE_AGREEMENTS
        );
    }

    private function canManageForAlliance(User $user, Alliance $alliance): bool
    {
        return $this->canCreateForAlliance($user, $alliance)
            || $this->allianceJobManager->hasUserPermission(
                $user,
                $alliance,
                AllianceJobPermissionEnum::DIPLOMATIC
            )
            || $this->allianceJobManager->hasUserPermission(
                $user,
                $alliance,
                AllianceJobPermissionEnum::EDIT_DIPLOMATIC_DOCUMENTS
            );
    }
}
