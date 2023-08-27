<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditPassable;

interface EditPassableRequestInterface
{
    public function getFieldId(): int;

    public function getPassable(): int;
}
