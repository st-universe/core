<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowArchiveSearchResult;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowArchiveSearchResultRequest implements ShowArchiveSearchResultRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getSearchId(): int
    {
        return $this->parameter('search')->int()->defaultsTo(0);
    }

    #[Override]
    public function getSearchString(): string
    {
        return trim($this->parameter('search')->string()->defaultsToIfEmpty(''));
    }

    #[Override]
    public function getVersion(): string
    {
        return $this->parameter('version')->string()->defaultsToIfEmpty('');
    }
}
