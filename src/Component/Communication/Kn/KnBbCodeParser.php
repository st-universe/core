<?php

namespace Stu\Component\Communication\Kn;

use JBBCode\Parser;
use Stu\Lib\StuBbCodeHtmlSafeVisitor;

class KnBbCodeParser extends Parser
{
    public function __construct()
    {
        $this->addCodeDefinitionSet(new KnBbCodeDefinitionSet());
    }

    public function parseToHtml(string $text): string
    {
        $this->parse($text)->accept(new StuBbCodeHtmlSafeVisitor());

        return $this->getAsHTML();
    }
}
