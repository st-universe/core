<?php

namespace Stu\Lib;

use JBBCode\Parser;

final class ParserWithImage implements ParserWithImageInterface
{
    public function __construct(private Parser $bbcodeParser) {}

    #[\Override]
    public function parse($str)
    {
        $this->bbcodeParser->parse($str)->accept(new StuBbCodeImageVisitor());

        return $this->bbcodeParser;
    }
}
