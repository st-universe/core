<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Stu\Component\Alliance\AllianceEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;

/**
 * Some locations require to determine if a certain user is friendly towards the player
 */
final class PlayerRelationDeterminator implements PlayerRelationDeterminatorInterface
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

    public function isFriend(UserInterface $user, UserInterface $otherUser): bool
    {
        $alliance = $user->getAlliance();

        $otherUserAlliance = $otherUser->getAlliance();

        if ($alliance !== null && $otherUserAlliance !== null) {
            if ($alliance === $otherUserAlliance) {
                return true;
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
                return true;
            }
        }

        $contact = $this->contactRepository->getByUserAndOpponent(
            $user->getId(),
            $otherUser->getId()
        );

        return $contact !== null && $contact->isFriendly();
    }

    public function isEnemy(UserInterface $user, UserInterface $otherUser): bool
    {
        $alliance = $user->getAlliance();

        $otherUserAlliance = $otherUser->getAlliance();

        if ($alliance !== null && $otherUserAlliance !== null) {
            if ($alliance === $otherUserAlliance) {
                return false;
            }

            $result = $this->allianceRelationRepository->getActiveByTypeAndAlliancePair(
                [
                    AllianceEnum::ALLIANCE_RELATION_WAR,
                ],
                $otherUserAlliance->getId(),
                $alliance->getId()
            );

            if ($result !== null) {
                return true;
            }
        }

        $contact = $this->contactRepository->getByUserAndOpponent(
            $user->getId(),
            $otherUser->getId()
        );

        return $contact !== null && $contact->isEnemy();
    }
}
