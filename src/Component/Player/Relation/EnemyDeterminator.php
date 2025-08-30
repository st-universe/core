<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;

class EnemyDeterminator
{
    public function __construct(private AllianceRelationRepositoryInterface $allianceRelationRepository, private ContactRepositoryInterface $contactRepository) {}

    public function isEnemy(User $user, User $otherUser): PlayerRelationTypeEnum
    {
        $alliance = $user->getAlliance();

        $otherUserAlliance = $otherUser->getAlliance();

        if ($alliance !== null && $otherUserAlliance !== null) {
            if ($alliance->getId() === $otherUserAlliance->getId()) {
                return PlayerRelationTypeEnum::NONE;
            }

            $result = $this->allianceRelationRepository->getActiveByTypeAndAlliancePair(
                [
                    AllianceRelationTypeEnum::WAR->value,
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

        if ($contact !== null && $contact->isEnemy()) {
            return PlayerRelationTypeEnum::USER;
        }

        return PlayerRelationTypeEnum::NONE;
    }
}
