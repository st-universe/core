<?php

namespace Stu\Lib\Interaction\Member;

use RuntimeException;
use Stu\Component\Spacecraft\Nbs\NbsUtilityInterface;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Trumfield;

class InteractionMemberFactory implements InteractionMemberFactoryInterface
{
    public function __construct(
        private NbsUtilityInterface $nbsUtility,
        private TholianWebUtilInterface $tholianWebUtil,
        private CommodityTransferInterface $commodityTransfer
    ) {}

    #[\Override]
    public function createMember(EntityWithInteractionCheckInterface $entity): InteractionMemberInterface
    {
        if ($entity instanceof Colony) {
            return new ColonyMember($this->tholianWebUtil, $entity);
        }
        if ($entity instanceof Spacecraft) {
            return new SpacecraftMember(
                $this->nbsUtility,
                $this->tholianWebUtil,
                $this->commodityTransfer,
                $entity
            );
        }
        if ($entity instanceof Trumfield) {
            return new TrumfieldMember($entity);
        }

        throw new RuntimeException('unknown entity class');
    }
}
