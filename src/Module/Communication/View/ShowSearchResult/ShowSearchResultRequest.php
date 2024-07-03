<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowSearchResult;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSearchResultRequest implements ShowSearchResultRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getSearchId(): int
    {
        return $this->queryParameter('search')->int()->defaultsTo(0);
    }

    #[Override]
    public function getSearchString(): string
    {
        return trim($this->queryParameter('search')->string()->defaultsToIfEmpty(''));
    }
}
