<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;

interface UserRelationManagerInterface
{
    public function getRepresentedParty(User $user): User|Alliance|null;

    public function canManageRelations(User $user): bool;

    public function create(
        User $actor,
        User|Alliance $source,
        User|Alliance $recipient,
        AllianceRelationTypeEnum $type,
        int $permissions = 0
    ): ?Relation;

    public function accept(User $actor, Relation $relation): bool;

    public function proposePermissionChange(User $actor, Relation $relation, int $permissions): bool;

    public function acceptPermissionChange(User $actor, Relation $relation): bool;

    public function declinePermissionChange(User $actor, Relation $relation): bool;

    public function cancelPermissionChange(User $actor, Relation $relation): bool;

    public function cancel(User $actor, Relation $relation): bool;

    public function decline(User $actor, Relation $relation): bool;

    public function suggestPeace(User $actor, Relation $relation): bool;

    public function removeRelationsForAllianceEntry(
        User $user,
        Alliance $alliance,
        bool $isAllianceCreation = false
    ): void;
}
