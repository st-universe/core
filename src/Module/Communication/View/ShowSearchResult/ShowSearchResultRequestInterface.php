<?php

namespace Stu\Module\Communication\View\ShowSearchResult;

interface ShowSearchResultRequestInterface
{
    public function getSearchId(): int;

    public function getSearchString(): string;
}
