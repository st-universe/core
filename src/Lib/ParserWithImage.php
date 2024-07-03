<?php

namespace Stu\Lib;

use JBBCode\Parser;
use Override;

final class ParserWithImage implements ParserWithImageInterface
{
    public function __construct(private Parser $bbcodeParser)
    {
    }

    #[Override]
    public function parse($str)
    {
        return $this->bbcodeParser->parse($str);
    }
}
