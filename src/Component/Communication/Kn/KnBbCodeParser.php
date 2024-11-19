<?php

namespace Stu\Component\Communication\Kn;

use JBBCode\Parser;

class KnBbCodeParser extends Parser
{
    public function __construct()
    {
        $this->addCodeDefinitionSet(new KnBbCodeDefinitionSet());
    }
}
