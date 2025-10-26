<?php

namespace Stu\Lib\Interaction;

use Stu\Lib\Interaction\Builder\SourceSetup;
use Stu\Lib\Interaction\Member\InteractionMemberFactoryInterface;

class InteractionCheckerBuilderFactory implements InteractionCheckerBuilderFactoryInterface
{
    public function __construct(
        private InteractionMemberFactoryInterface $interactionMemberFactory
    ) {}

    #[\Override]
    public function createInteractionChecker(): SourceSetup
    {
        $customizedInteractionChecker = new CustomizedInteractionChecker();

        return new SourceSetup(
            $this->interactionMemberFactory,
            $customizedInteractionChecker
        );
    }
}
