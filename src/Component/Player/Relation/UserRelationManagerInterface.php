<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRelation;

interface UserRelationManagerInterface
{
    public function getRepresentedParty(User $user): User|Alliance|null;

    public function canManageRelations(User $user): bool;

    public function create(User $actor, User|Alliance $source, User|Alliance $recipient, AllianceRelationTypeEnum $type): ?UserRelation;

    public function accept(User $actor, UserRelation $relation): bool;

    public function cancel(User $actor, UserRelation $relation): bool;

    public function decline(User $actor, UserRelation $relation): bool;

    public function suggestPeace(User $actor, UserRelation $relation): bool;

    public function removeRelationsForAllianceEntry(User $user, Alliance $alliance, bool $isAllianceCreation = false): void;
}
