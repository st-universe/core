<?php

declare(strict_types=1);

namespace Stu\Module\Research\View\ShowResearch;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowResearchRequest implements ShowResearchRequestInterface
{
    use CustomControllerHelperTrait;

    public function getResearchId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
