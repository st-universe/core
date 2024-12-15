<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Orm\Entity\UserInterface;

interface PlayerRelationDeterminatorInterface
{
    /**
     * Checks if $otherUser is a friend of $user
     */
    public function isFriend(?UserInterface $user, ?UserInterface $otherUser): bool;

    /**
     * Checks if $otherUser is an enemy of $user
     */
    public function isEnemy(UserInterface $user, UserInterface $otherUser): bool;
}
