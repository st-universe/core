<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SuggestPeace;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class SuggestPeaceRequest implements SuggestPeaceRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getRelationId(): int
    {
        return $this->queryParameter('al')->int()->required();
    }
}
