<?php

declare(strict_types=1);

namespace Stu\Module\Research\View\ShowResearch;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowResearchRequest implements ShowResearchRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getResearchId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
