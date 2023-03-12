<?php

declare(strict_types=1);

namespace Stu\Component\Player;


use Stu\Orm\Entity\UserInterface;

interface PlayerRelationDeterminatorInterface
{
    /**
     * Checks if $otherUser is a friend of $user
     */
    public function isFriend(UserInterface $user, UserInterface $otherUser): bool;
}