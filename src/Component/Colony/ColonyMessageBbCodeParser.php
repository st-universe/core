<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use JBBCode\Parser;
use Stu\Lib\StuBbCodeImageVisitor;

final class ColonyMessageBbCodeParser extends Parser
{
    public function __construct()
    {
        $this->addCodeDefinitionSet(new ColonyMessageBbCodeDefinitionSet());
    }
    #[\Override]
    public function parse($str)
    {
        parent::parse($str)->accept(new StuBbCodeImageVisitor());

        return $this;
    }
}
