<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\StartResearch;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class StartResearchRequest implements StartResearchRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getResearchId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
