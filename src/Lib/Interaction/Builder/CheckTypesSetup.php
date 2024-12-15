<?php

namespace Stu\Lib\Interaction\Builder;

use Stu\Lib\Interaction\CustomizedInteractionChecker;
use Stu\Lib\Interaction\InteractionCheckType;

class CheckTypesSetup
{
    public function __construct(
        private CustomizedInteractionChecker $interactionChecker
    ) {}

    /** @param array<InteractionCheckType> $checkTypes */
    public function setCheckTypes(array $checkTypes): CustomizedInteractionChecker
    {
        $this->interactionChecker->setCheckTypes($checkTypes);

        return $this->interactionChecker;
    }
}
