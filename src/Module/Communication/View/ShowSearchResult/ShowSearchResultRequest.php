<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowSearchResult;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSearchResultRequest implements ShowSearchResultRequestInterface
{
    use CustomControllerHelperTrait;

    public function getSearchId(): int
    {
        return $this->queryParameter('search')->int()->defaultsTo(0);
    }

    public function getSearchString(): string
    {
        return trim($this->queryParameter('search')->string()->defaultsToIfEmpty(''));
    }
}
