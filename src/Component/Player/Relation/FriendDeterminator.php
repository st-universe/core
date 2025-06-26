<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\AllianceEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;

class FriendDeterminator
{
    public function __construct(private AllianceRelationRepositoryInterface $allianceRelationRepository, private ContactRepositoryInterface $contactRepository)
    {
    }

    public function isFriend(User $user, User $otherUser): PlayerRelationTypeEnum
    {
        $alliance = $user->getAlliance();

        $otherUserAlliance = $otherUser->getAlliance();

        if ($alliance !== null && $otherUserAlliance !== null) {
            if ($alliance === $otherUserAlliance) {
                return PlayerRelationTypeEnum::ALLY;
            }

            $result = $this->allianceRelationRepository->getActiveByTypeAndAlliancePair(
                [
                    AllianceEnum::ALLIANCE_RELATION_FRIENDS,
                    AllianceEnum::ALLIANCE_RELATION_ALLIED,
                    AllianceEnum::ALLIANCE_RELATION_VASSAL
                ],
                $otherUserAlliance->getId(),
                $alliance->getId()
            );

            if ($result !== null) {
                return PlayerRelationTypeEnum::ALLY;
            }
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
