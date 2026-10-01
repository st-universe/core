<?php

namespace Stu\Lib\Interaction;

use Stu\Extension\ExtensionHooks;
use Stu\Lib\Interaction\Builder\SourceSetup;
use Stu\Lib\Interaction\Member\InteractionMemberFactoryInterface;

class InteractionCheckerBuilderFactory implements InteractionCheckerBuilderFactoryInterface
{
    public function __construct(
        private InteractionMemberFactoryInterface $interactionMemberFactory,
        private ?ExtensionHooks $extensions = null
    ) {}

    #[\Override]
    public function createInteractionChecker(): SourceSetup
    {
        $customizedInteractionChecker = new CustomizedInteractionChecker($this->extensions);

        return new SourceSetup(
            $this->interactionMemberFactory,
            $customizedInteractionChecker
        );
    }
}
