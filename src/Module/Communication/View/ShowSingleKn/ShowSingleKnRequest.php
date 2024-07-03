<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowSingleKn;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSingleKnRequest implements ShowSingleKnRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPostId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
