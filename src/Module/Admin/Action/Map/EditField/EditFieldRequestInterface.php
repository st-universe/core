<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditField;

interface EditFieldRequestInterface
{
    public function getFieldId(): int;

    public function getFieldType(): int;
}
