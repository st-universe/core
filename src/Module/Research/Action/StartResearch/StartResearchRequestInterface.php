<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\StartResearch;

interface StartResearchRequestInterface
{
    public function getResearchId(): int;
}
