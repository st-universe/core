<?php

namespace Stu\Lib\Interaction\Member;

use Override;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;

class ColonyMember implements InteractionMemberInterface
{
    public function __construct(private ColonyInterface $colony) {}

    #[Override]
    public function canAccess(
        InteractionMemberInterface $other,
        callable $shouldCheck
    ): ?InteractionCheckType {
        return null;
    }

    #[Override]
    public function canBeAccessedFrom(
        InteractionMemberInterface $other,
        bool $isFriend,
        callable $shouldCheck
    ): ?InteractionCheckType {

        return null;
    }

    #[Override]
    public function getLocation(): MapInterface|StarSystemMapInterface
    {
        return $this->colony->getLocation();
    }

    #[Override]
    public function getUser(): ?UserInterface
    {
        return $this->colony->getUser();
    }
}
