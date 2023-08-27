<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditBorder;

interface EditBorderRequestInterface
{
    public function getFieldId(): int;

    public function getBorder(): int;
}
