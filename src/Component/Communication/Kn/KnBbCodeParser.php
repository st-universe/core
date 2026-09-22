<?php

namespace Stu\Component\Communication\Kn;

use JBBCode\Parser;
use JBBCode\visitors\HTMLSafeVisitor;

class KnBbCodeParser extends Parser
{
    public function __construct()
    {
        $this->addCodeDefinitionSet(new KnBbCodeDefinitionSet());
    }

    public function parseToHtml(string $text): string
    {
        $this->parse($text)->accept(new HTMLSafeVisitor());

        return $this->getAsHTML();
    }
}
