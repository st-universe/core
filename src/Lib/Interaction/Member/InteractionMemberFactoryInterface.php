<?php

namespace Stu\Lib\Interaction\Member;

use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;

interface InteractionMemberFactoryInterface
{
    public function createMember(EntityWithInteractionCheckInterface $entity): InteractionMemberInterface;
}
