<?php

namespace Stu\Module\Communication\View\ShowArchiveSearchResult;

interface ShowArchiveSearchResultRequestInterface
{
    public function getSearchId(): int;

    public function getSearchString(): string;

    public function getVersion(): string;
}
