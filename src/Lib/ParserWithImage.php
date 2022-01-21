<?php

namespace Stu\Lib;

use JBBCode\Parser;

final class ParserWithImage implements ParserWithImageInterface
{
    private Parser $bbcodeParser;

    public function __construct(
        Parser $bbcodeParser
    ) {
        $this->bbcodeParser = $bbcodeParser;
    }

    public function parse($str)
    {
        return $this->bbcodeParser->parse($str);
    }
}
