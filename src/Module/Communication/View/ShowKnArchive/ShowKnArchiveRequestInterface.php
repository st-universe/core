<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchive;

interface ShowKnArchiveRequestInterface
{
    public function getVersion(): string;

    public function getPage(): int;

    public function getMark(): int;
}
