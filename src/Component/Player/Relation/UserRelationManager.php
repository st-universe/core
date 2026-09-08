<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;

final class UserRelationManager implements UserRelationManagerInterface
{
    public function __construct(
        private readonly UserRelationAccessChecker $accessChecker,
        private readonly UserRelationOfferManager $offerManager,
        private readonly UserRelationLifecycleManager $lifecycleManager,
        private readonly UserRelationPermissionManager $permissionManager,
        private readonly UserRelationPeaceManager $peaceManager,
        private readonly UserRelationAllianceEntryManager $allianceEntryManager
    ) {}

    #[\Override]
    public function getRepresentedParty(User $user): User|Alliance|null
    {
        return $this->accessChecker->getRepresentedParty($user);
    }

    #[\Override]
    public function canManageRelations(User $user): bool
    {
        return $this->accessChecker->canManageRelations($user);
    }

    #[\Override]
    public function create(
        User $actor,
        User|Alliance $source,
        User|Alliance $recipient,
        AllianceRelationTypeEnum $type,
        int $permissions = 0
    ): ?Relation {
        return $this->offerManager->create($actor, $source, $recipient, $type, $permissions);
    }

    #[\Override]
    public function accept(User $actor, Relation $relation): bool
    {
        return $this->lifecycleManager->accept($actor, $relation);
    }

    #[\Override]
    public function proposePermissionChange(User $actor, Relation $relation, int $permissions): bool
    {
        return $this->permissionManager->propose($actor, $relation, $permissions);
    }

    #[\Override]
    public function acceptPermissionChange(User $actor, Relation $relation): bool
    {
        return $this->permissionManager->accept($actor, $relation);
    }

    #[\Override]
    public function declinePermissionChange(User $actor, Relation $relation): bool
    {
        return $this->permissionManager->decline($actor, $relation);
    }

    #[\Override]
    public function cancelPermissionChange(User $actor, Relation $relation): bool
    {
        return $this->permissionManager->cancel($actor, $relation);
    }

    #[\Override]
    public function cancel(User $actor, Relation $relation): bool
    {
        return $this->lifecycleManager->cancel($actor, $relation);
    }

    #[\Override]
    public function decline(User $actor, Relation $relation): bool
    {
        return $this->lifecycleManager->decline($actor, $relation);
    }

    #[\Override]
    public function suggestPeace(User $actor, Relation $relation): bool
    {
        return $this->peaceManager->suggest($actor, $relation);
    }

    #[\Override]
    public function removeRelationsForAllianceEntry(
        User $user,
        Alliance $alliance,
        bool $isAllianceCreation = false
    ): void {
        $this->allianceEntryManager->removeFor($user, $alliance, $isAllianceCreation);
    }
}
