<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Override;
use Stu\Orm\Entity\UserInterface;

/**
 * Some locations require to determine if a certain user is friendly towards the player
 */
final class PlayerRelationDeterminator implements PlayerRelationDeterminatorInterface
{
    public function __construct(
        private FriendDeterminator $friendDeterminator,
        private EnemyDeterminator $enemyDeterminator
    ) {
    }

    #[Override]
    public function isFriend(UserInterface $user, UserInterface $otherUser): bool
    {
        $friendRelation = $this->friendDeterminator->isFriend($user, $otherUser);
        if ($friendRelation->isDominant()) {
            return true;
        }

        $enemyRelation = $this->enemyDeterminator->isEnemy($user, $otherUser);
        if ($enemyRelation->isDominant()) {
            return false;
        }

        return $friendRelation !== PlayerRelationTypeEnum::NONE;
    }

    #[Override]
    public function isEnemy(UserInterface $user, UserInterface $otherUser): bool
    {
        $enemyRelation = $this->enemyDeterminator->isEnemy($user, $otherUser);
        if ($enemyRelation->isDominant()) {
            return true;
        }

        $friendRelation = $this->friendDeterminator->isFriend($user, $otherUser);
        if ($friendRelation->isDominant()) {
            return false;
        }

        return $enemyRelation !== PlayerRelationTypeEnum::NONE;
    }
}
