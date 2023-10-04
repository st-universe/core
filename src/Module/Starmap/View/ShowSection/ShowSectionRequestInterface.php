<?php

namespace Stu\Module\Starmap\View\ShowSection;

interface ShowSectionRequestInterface
{
    public function getLayerId(): int;

    public function getSection(): int;

    public function getDirection(): ?int;
}
