<?php

namespace Stu\Lib\Interaction\Member;

use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Lib\Map\EntityWithLocationInterface;
use Stu\Orm\Entity\UserInterface;

interface InteractionMemberInterface extends EntityWithLocationInterface
{
    /** @param callable(InteractionCheckType):bool $shouldCheck */
    public function canAccess(
        InteractionMemberInterface $other,
        callable $shouldCheck
    ): ?InteractionCheckType;

    /** @param callable(InteractionCheckType):bool $shouldCheck */
    public function canBeAccessedFrom(
        InteractionMemberInterface $other,
        callable $shouldCheck
    ): ?InteractionCheckType;

    public function get(): EntityWithInteractionCheckInterface;

    public function getUser(): ?UserInterface;
}
