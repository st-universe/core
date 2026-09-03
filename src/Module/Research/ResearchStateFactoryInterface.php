<?php

declare(strict_types=1);

namespace Stu\Module\Research;

interface ResearchStateFactoryInterface
{
    public function createResearchState(): ResearchStateInterface;
}
