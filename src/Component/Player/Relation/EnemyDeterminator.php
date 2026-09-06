<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;

class EnemyDeterminator
{
    public function __construct(
        private RelationRepositoryInterface $relationRepository,
        private ContactRepositoryInterface $contactRepository
    ) {}

    public function isEnemy(User $user, User $otherUser): PlayerRelationTypeEnum
    {
        $party = $user->getAlliance() ?? $user;
        $otherParty = $otherUser->getAlliance() ?? $otherUser;

        if ($party::class === $otherParty::class && $party->getId() === $otherParty->getId()) {
            return PlayerRelationTypeEnum::NONE;
        }

        $relation = $this->relationRepository->getActiveByParties(
            [AllianceRelationTypeEnum::WAR->value],
            $party,
            $otherParty
        );
        if ($relation !== null) {
            return PlayerRelationTypeEnum::ALLY;
        }

        $contact = $this->contactRepository->getByUserAndOpponent(
            $user->getId(),
            $otherUser->getId()
        );

        if ($contact !== null && $contact->isEnemy()) {
            return PlayerRelationTypeEnum::USER;
        }

        return PlayerRelationTypeEnum::NONE;
    }
}
