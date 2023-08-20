<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditSystemType;

interface EditSystemTypeRequestInterface
{
    public function getFieldId(): int;

    public function getSystemType(): int;
}
