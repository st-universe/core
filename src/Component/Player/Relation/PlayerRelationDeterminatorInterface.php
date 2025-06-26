<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Orm\Entity\User;

interface PlayerRelationDeterminatorInterface
{
    /**
     * Checks if $otherUser is a friend of $user
     */
    public function isFriend(?User $user, ?User $otherUser): bool;

    /**
     * Checks if $otherUser is an enemy of $user
     */
    public function isEnemy(User $user, User $otherUser): bool;
}
