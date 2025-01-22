<?php

namespace Stu\Lib\Interaction\Member;

use RuntimeException;
use Stu\Component\Spacecraft\Nbs\NbsUtilityInterface;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\TrumfieldInterface;

class InteractionMemberFactory implements InteractionMemberFactoryInterface
{
    public function __construct(
        private NbsUtilityInterface $nbsUtility,
        private TholianWebUtilInterface $tholianWebUtil,
        private CommodityTransferInterface $commodityTransfer
    ) {}

    public function createMember(EntityWithInteractionCheckInterface $entity): InteractionMemberInterface
    {
        if ($entity instanceof ColonyInterface) {
            return new ColonyMember($this->tholianWebUtil, $entity);
        }
        if ($entity instanceof SpacecraftInterface) {
            return new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $entity);
        }
        if ($entity instanceof TrumfieldInterface) {
            return new TrumfieldMember($entity);
        }

        throw new RuntimeException('unknown entity class');
    }
}
