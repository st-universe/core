<?php

namespace Stu\Module\Starmap\View\EditSection;

interface EditSectionRequestInterface
{
    public function getXCoordinate(): int;

    public function getYCoordinate(): int;

    public function getSectionId(): int;
}