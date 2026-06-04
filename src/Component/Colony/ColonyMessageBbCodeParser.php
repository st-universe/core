<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use JBBCode\Parser;

final class ColonyMessageBbCodeParser extends Parser
{
    public function __construct()
    {
        $this->addCodeDefinitionSet(new ColonyMessageBbCodeDefinitionSet());
    }
}
