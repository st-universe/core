<?php

namespace Stu\Lib\Interaction;

use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Lib\Interaction\Builder\SourceSetup;
use Stu\Lib\Interaction\Member\InteractionMemberFactoryInterface;

class InteractionCheckerBuilderFactory implements InteractionCheckerBuilderFactoryInterface
{
    public function __construct(
        private InteractionMemberFactoryInterface $interactionMemberFactory,
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator
    ) {}

    public function createInteractionChecker(): SourceSetup
    {
        $customizedInteractionChecker = new CustomizedInteractionChecker($this->playerRelationDeterminator);

        return new SourceSetup(
            $this->interactionMemberFactory,
            $customizedInteractionChecker
        );
    }
}
