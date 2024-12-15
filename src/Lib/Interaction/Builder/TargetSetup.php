<?php

namespace Stu\Lib\Interaction\Builder;

use Stu\Lib\Interaction\CustomizedInteractionChecker;
use Stu\Lib\Interaction\Member\InteractionMemberFactoryInterface;

class TargetSetup
{
    public function __construct(
        private InteractionMemberFactoryInterface $interactionMemberFactory,
        private CustomizedInteractionChecker $interactionChecker
    ) {}

    public function setTarget(mixed $target): CheckTypesSetup
    {
        $interactionMember = $this->interactionMemberFactory->createMember($target);
        $this->interactionChecker->setTarget($interactionMember);

        return new CheckTypesSetup($this->interactionChecker);
    }
}
