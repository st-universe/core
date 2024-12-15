<?php

namespace Stu\Lib\Interaction\Member;

use RuntimeException;
use Stu\Component\Spacecraft\Nbs\NbsUtilityInterface;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\TrumfieldInterface;

class InteractionMemberFactory implements InteractionMemberFactoryInterface
{
    public function __construct(
        private NbsUtilityInterface $nbsUtility
    ) {}

    public function createMember(EntityWithInteractionCheckInterface $entity): InteractionMemberInterface
    {
        if ($entity instanceof ColonyInterface) {
            return new ColonyMember($entity);
        }
        if ($entity instanceof SpacecraftInterface) {
            return new SpacecraftMember($this->nbsUtility, $entity);
        }
        if ($entity instanceof TrumfieldInterface) {
            return new TrumfieldMember($entity);
        }

        throw new RuntimeException('unknown entity class');
    }
}
