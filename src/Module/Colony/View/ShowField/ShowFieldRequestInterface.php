<?php

namespace Stu\Module\Colony\View\ShowField;

interface ShowFieldRequestInterface
{
    public function getColonyId(): int;

    public function getFieldId(): int;
}
