<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowSearchResult;

interface ShowSearchResultRequestInterface
{
    public function getSearchId(): int;

    public function getSearchString(): string;
}
