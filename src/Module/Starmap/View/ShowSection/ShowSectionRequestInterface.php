<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSection;

interface ShowSectionRequestInterface
{
    public function getLayerId(): int;

    public function getSection(): int;

    public function getDirection(): ?int;
}
