<?php

namespace Stu\Lib\Interaction;

use Stu\Lib\Interaction\Builder\SourceSetup;

interface InteractionCheckerBuilderFactoryInterface
{
    public function createInteractionChecker(): SourceSetup;
}
