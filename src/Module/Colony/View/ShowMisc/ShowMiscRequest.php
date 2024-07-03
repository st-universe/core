<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowMisc;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowMiscRequest implements ShowMiscRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
