<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\ShowRelationText;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowRelationTextRequest implements ShowRelationTextRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getRelationId(): int
    {
        return $this->queryParameter('relationid')->int()->defaultsTo(0);
    }
}
