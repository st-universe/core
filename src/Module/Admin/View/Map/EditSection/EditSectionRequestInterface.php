<?php

namespace Stu\Module\Admin\View\Map\EditSection;

interface EditSectionRequestInterface
{
    public function getXCoordinate(): int;

    public function getYCoordinate(): int;

    public function getSectionId(): int;
}
