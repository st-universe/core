<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;

class FriendDeterminator
{
    public function __construct(
        private RelationRepositoryInterface $relationRepository,
        private ContactRepositoryInterface $contactRepository
    ) {}

    public function isFriend(User $user, User $otherUser): PlayerRelationTypeEnum
    {
        $party = $user->getAlliance() ?? $user;
        $otherParty = $otherUser->getAlliance() ?? $otherUser;

        if ($party::class === $otherParty::class && $party->getId() === $otherParty->getId()) {
            return PlayerRelationTypeEnum::ALLY;
        }

        $relation = $this->relationRepository->getActiveByParties([], $party, $otherParty);
        if ($relation?->hasPermissionFor($user, RelationPermissionEnum::FRIENDLY)) {
            return PlayerRelationTypeEnum::ALLY;
        }

        $contact = $this->contactRepository->getByUserAndOpponent(
            $user->getId(),
            $otherUser->getId()
        );

        if ($contact !== null && $contact->isFriendly()) {
            return PlayerRelationTypeEnum::USER;
        }

        return PlayerRelationTypeEnum::NONE;
    }
}
