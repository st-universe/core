<?php

namespace Stu\Lib\Interaction\Member;

use Override;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;

class ColonyMember implements InteractionMemberInterface
{
    public function __construct(
        private TholianWebUtilInterface $tholianWebUtil,
        private ColonyInterface $colony
    ) {}

    #[Override]
    public function get(): ColonyInterface
    {
        return $this->colony;
    }

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
        callable $shouldCheck
    ): ?InteractionCheckType {

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM)
            && $this->colony->getLocation()->hasAnomaly(AnomalyTypeEnum::ION_STORM)
        ) {
            return InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB)
            && $this->tholianWebUtil->isTargetOutsideFinishedTholianWeb($other->get(), $this->colony)
        ) {
            return InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB;
        }

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
