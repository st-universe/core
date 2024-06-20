<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

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

    public function isFriend(UserInterface $user, UserInterface $otherUser): bool
    {
        return $this->friendDeterminator->isFriend($user, $otherUser)
            && !$this->enemyDeterminator->isEnemy($user, $otherUser);
    }

    public function isEnemy(UserInterface $user, UserInterface $otherUser): bool
    {
        return $this->enemyDeterminator->isEnemy($user, $otherUser)
            && !$this->friendDeterminator->isFriend($user, $otherUser);
    }
}
