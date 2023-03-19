<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\StartResearch;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class StartResearchRequest implements StartResearchRequestInterface
{
    use CustomControllerHelperTrait;

    public function getResearchId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
