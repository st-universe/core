<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\AllianceEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;

class FriendDeterminator
{
    private AllianceRelationRepositoryInterface $allianceRelationRepository;

    private ContactRepositoryInterface $contactRepository;

    public function __construct(
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->contactRepository = $contactRepository;
    }

    public function isFriend(UserInterface $user, UserInterface $otherUser): PlayerRelationTypeEnum
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
