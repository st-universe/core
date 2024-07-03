<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildPlans;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowBuildPlansRequest implements ShowBuildPlansRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
