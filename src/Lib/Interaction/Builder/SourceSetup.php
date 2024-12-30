<?php

namespace Stu\Lib\Interaction\Builder;

use Stu\Lib\Interaction\CustomizedInteractionChecker;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Lib\Interaction\Member\InteractionMemberFactoryInterface;

class SourceSetup
{
    public function __construct(
        private InteractionMemberFactoryInterface $interactionMemberFactory,
        private CustomizedInteractionChecker $interactionChecker
    ) {}

    public function setSource(EntityWithInteractionCheckInterface $source): TargetSetup
    {
        $interactionMember = $this->interactionMemberFactory->createMember($source);
        $this->interactionChecker->setSource($interactionMember);

        return new TargetSetup(
            $this->interactionMemberFactory,
            $this->interactionChecker
        );
    }
}
