<?php

declare(strict_types=1);

namespace Stu\Lib;

use JBBCode\Parser;

interface ParserWithImageInterface
{
    /**
     * @param string $str
     * @return Parser
     */
    public function parse($str);
}
