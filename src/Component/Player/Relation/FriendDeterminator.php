<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\UserRelationRepositoryInterface;

class FriendDeterminator
{
    public function __construct(
        private AllianceRelationRepositoryInterface $allianceRelationRepository,
        private ContactRepositoryInterface $contactRepository,
        private ?UserRelationRepositoryInterface $userRelationRepository = null
    ) {}

    public function isFriend(User $user, User $otherUser): PlayerRelationTypeEnum
    {
        $alliance = $user->getAlliance();

        $otherUserAlliance = $otherUser->getAlliance();

        if ($alliance !== null && $otherUserAlliance !== null) {
            if ($alliance->getId() === $otherUserAlliance->getId()) {
                return PlayerRelationTypeEnum::ALLY;
            }

            $result = $this->allianceRelationRepository->getActiveByTypeAndAlliancePair(
                [
                    AllianceRelationTypeEnum::FRIENDS->value,
                    AllianceRelationTypeEnum::ALLIED->value,
                    AllianceRelationTypeEnum::VASSAL->value
                ],
                $otherUserAlliance->getId(),
                $alliance->getId()
            );

            if ($result !== null) {
                return PlayerRelationTypeEnum::ALLY;
            }
        } elseif ($this->userRelationRepository !== null) {
            $result = $alliance !== null
                ? $this->userRelationRepository->getActiveByAllianceAndUserPair(
                    [
                        AllianceRelationTypeEnum::FRIENDS->value,
                        AllianceRelationTypeEnum::ALLIED->value,
                        AllianceRelationTypeEnum::VASSAL->value,
                    ],
                    $alliance->getId(),
                    $otherUser->getId()
                )
                : ($otherUserAlliance !== null
                    ? $this->userRelationRepository->getActiveByAllianceAndUserPair(
                        [
                            AllianceRelationTypeEnum::FRIENDS->value,
                            AllianceRelationTypeEnum::ALLIED->value,
                            AllianceRelationTypeEnum::VASSAL->value,
                        ],
                        $otherUserAlliance->getId(),
                        $user->getId()
                    )
                    : $this->userRelationRepository->getActiveByUserPair(
                        [
                            AllianceRelationTypeEnum::FRIENDS->value,
                            AllianceRelationTypeEnum::ALLIED->value,
                            AllianceRelationTypeEnum::VASSAL->value,
                        ],
                        $user->getId(),
                        $otherUser->getId()
                    ));

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
